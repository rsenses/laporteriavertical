<?php

namespace App\Services;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Exception;
use GuzzleHttp\Client;
use Respect\Validation\Validator as v;
use flight\net\Request;
use App\Entities\Poll;

class Contest
{
    public $data;
    public $status = true;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function validatePostData()
    {
        $validAccount = v::ArrayVal()
            ->key('favorito', v::StringType()->notEmpty()->length(3, 100));

        $validAccount->assert($this->data);

        return $this;
    }

    public function storeInDatabase(MyPDO $db, string $slug, Request $request)
    {
        // $this->guardAgainstDuplicatedRegistration($db, $slug);
        $poll = Poll::fetchByPoll($db, $slug, 1);
        $poll = $poll[0];

        $array = json_decode($poll->json);
        $request = json_encode($request);
        $createdAt = date('Y-m-d H:i:s', time());

        $jugada = $this->data - 1;
        ++$array[$jugada];

        $json = json_encode($array);

        try {
            $this->status = Poll::update($db, $json, $slug, $request, $createdAt);

            return $array;
        } catch (\Throwable $th) {
            throw new Exception('Error en la inscripción, inténtalo de nuevo más tarde.', 1);
        }
    }

    public function storeInDatabaseNative(MyPDO $db, string $slug, Request $request)
    {
        // $this->guardAgainstDuplicatedRegistration($db, $slug);
        $poll = Poll::fetchByPoll($db, $slug, 1);
        $poll = $poll[0];

        $array = json_decode($poll->json);
        $request = json_encode($request);
        $createdAt = date('Y-m-d H:i:s', time());

        // $jugada = $this->data - 1;
        // ++$array[$jugada];
        $data = explode(':', $this->data);

        $position = $data[0];
        $favorite = $data[1];

        ++$array->$position[$favorite];

        $json = json_encode($array);

        try {
            $this->status = Poll::update($db, $json, $slug, $request, $createdAt);

            return $array;
        } catch (\Throwable $th) {
            throw new Exception('Error en la inscripción, inténtalo de nuevo más tarde.', 1);
        }
    }

    private function guardAgainstDuplicatedRegistration(MyPDO $db, string $slug)
    {
        $polls = Poll::fetchByPoll($db, $slug);

        foreach ($polls as $poll) {
            $data = json_decode($poll->json, true);

            if ($data['email'] === $this->data['email']) {
                throw new Exception('Ya tenemos una inscripción con tu email para este evento.', 1);
            }
        }
    }

    public function sendToTrackit()
    {
        $client = new Client($GLOBALS['env']['api']);

        try {
            $res = $client->request('POST', 'registrations', [
                'headers' => [
                    'Authorization' => "Bearer {$GLOBALS['env']['api_token']}",
                    'Content-Language' => 'es',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'first_name' => $this->data['name'],
                    'last_name' => $this->data['surname'],
                    'email' => $this->data['email'],
                    'phone' => $this->data['phone'],
                    'contact' => $this->data['contact'],
                    'nif' => $this->data['dni'],
                    'product_id' => $this->data['product_id'],
                    'registration_type' => 'asistente',
                    'transition' => 'approve',
                ]
            ]);

            $response = json_decode($res->getBody());

            $this->status = true;

            return $this;
        } catch (\Throwable $th) {
            die(var_dump($th));
            error_log('Fallo en request a API: ' . $th->getMessage());

            throw new Exception('Error al enviar la inscripción, inténtalo de nuevo más tarde.', 1);
        }
    }

    public function sendToDatacentric()
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://registropg.com/es/urfwsRest/api/',
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);

        $response = $client->request('GET', 'basicdata', [
            'query' => [
                'accountId' => $GLOBALS['config']['datacentric']['accountId'],
                'incentiveId' => $GLOBALS['config']['datacentric']['incentiveId'],
                'email' => $this->data['email'],
                'name' => $this->data['name'],
                'surname' => $this->data['surname'],
                'mobile' => $this->data['mobile'],
                'city' => $this->data['city'],
                'gender' => $this->data['gender'],
                'birthdate' => $this->data['birthdate'],
            ]
        ]);

        $body = $response->getBody()->getContents();
        $body = (string)substr($body, 1, -1);
        $body = str_replace('\r\n', '', $body);
        $body = stripslashes($body);

        $sxe = simplexml_load_string($body);

        if ($sxe->Result == 'ERROR') {
            $this->status = false;

            foreach ($sxe->Errors as $error) {
                $error = $GLOBALS['config']['datacentric']['errors'][(string)$error->Error] ?? 'Ha habido un error al enviar tu concurso, inténtalo de nuevo mas tarde.';

                throw new Exception($error, 500);
            }
        }

        return $this;
    }

    public function sendToAzure()
    {
        $container = 'files';
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName=' . $GLOBALS['env']['azure']['blob']['AccountName'] . ';AccountKey=' . $GLOBALS['env']['azure']['blob']['AccountKey'];

        try {
            $content = fopen($GLOBALS['config']['csv'], 'r');

            $blobClient = BlobRestProxy::createBlobService($connectionString);

            $blobClient->createBlockBlob($container, $GLOBALS['config']['web_slug'] . '/contest/contest.csv', $content);

            $this->uploadImage($blobClient, $container);
        } catch (ServiceException $e) {
            die(var_dump($e));
            throw new Exception($e, 500);
        }

        return $this;
    }

    private function uploadImage(BlobRestProxy $blobClient, $container)
    {
        try {
            $imageName = $this->setImageName();

            $content = fopen($this->data['file'], 'r');

            $blobClient->createBlockBlob($container, $GLOBALS['config']['web_slug'] . '/contest/' . $imageName, $content);
        } catch (ServiceException $e) {
            throw new Exception($e, 500);
        }
    }

    private function setImageName()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // devuelve el tipo mime de su extensión

        $mime = finfo_file($finfo, $this->data['file']);

        if (!array_key_exists($mime, $GLOBALS['config']['mime'])) {
            throw new Exception('Tipo de imagen no válida', 500);
        }

        finfo_close($finfo);

        return $this->data['email'] . '.' . $GLOBALS['config']['mime'][$mime];
    }

    private function generateCsv($delimiter = ',', $enclosure = '"')
    {
        $handle = fopen($GLOBALS['config']['csv'], 'a');

        $data = $this->data;
        unset($data['file']);

        fputcsv($handle, $data, $delimiter, $enclosure);
    }
}
