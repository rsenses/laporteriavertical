<?php

namespace App\Controllers;

use App\Services\Contest;
use Exception;

// use Respect\Validation\Exceptions\NestedValidationException;

class RegistrationController extends BaseController
{
    public function storeAction(string $category, string $slug)
    {
        // $status = false;
        $errors = [];

        $data = filter_input(INPUT_POST, 'favorito', FILTER_SANITIZE_STRING);

        try {
            $contest = new Contest($data);

            if ($category === 'native') {
                $response = $contest->storeInDatabaseNative($this->db, $slug, $this->app->request());
            } else {
                $response = $contest->storeInDatabase($this->db, $slug, $this->app->request());
            }

            // $status = $response->status;

            $data = null;

            header('Access-Control-Allow-Origin: *');

            return $this->app->json([
                'status' => 'success',
                'data' => $response
            ]);
            // } catch (NestedValidationException $invalidAccount) {
        //     $errors = $invalidAccount->findMessages(
        //         $GLOBALS['config']['form_errors']
        //     );

        //     return $this->app->json([
        //         'status' => 'error',
        //         'errors' => $errors,
        //     ]);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();

            return $this->app->json([
                'status' => 'error',
                'errors' => $errors,
            ]);
        }
    }
}
