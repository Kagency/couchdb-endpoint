var http = require('http');
var apiRegex = /^\/api\//;

http.createServer(function(request, response) {
    var port = 8080, proxy;
    if (apiRegex.test(request.url)) {
        port = 8081;
    }
    proxy = http.createClient(port, "localhost");

    console.log(request.connection.remoteAddress + " => " + port + ": " + request.method + " " + request.url);

    var proxy_request = proxy.request(request.method, request.url, request.headers);
    proxy_request.addListener('response', function(proxy_response) {
        proxy_response.addListener('data', function(chunk) {
            response.write(chunk, 'binary');
        });
        proxy_response.addListener('end', function() {
            response.end();
        });
        response.writeHead(proxy_response.statusCode, proxy_response.headers);
    });
    request.addListener('data', function(chunk) {
        proxy_request.write(chunk, 'binary');
    });
    request.addListener('end', function() {
        proxy_request.end();
    });
}).listen(8000);
