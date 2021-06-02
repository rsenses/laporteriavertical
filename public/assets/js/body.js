var domain = window.location.hostname.split('.');
if (domain[domain.length - 1] == 'localhost') {
    domain.pop();
}
domain = domain.slice(-2, -1).join('');
domain = domain ? domain : 'elmundo';

if (domain === 'expobeta') {
    domain = 'elmundo';
}

var head = document.getElementsByTagName('head')[0];

var favicon = document.createElement('link');
favicon.rel = 'shortcut icon';
favicon.href = '/favicon-' + domain + '.ico';

head.appendChild(favicon);

document.body.classList.add(domain);