<?php
// Swagger UI Template
// This template is included by viewer.php when renderer is 'swagger'
?>

<link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.24.0/swagger-ui.css" />

<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.24.0/swagger-ui-bundle.js"></script>
<script>
    SwaggerUIBundle({
        url: 'viewer.php?id=<?php echo $fileId; ?>&spec=1',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIBundle.presets.standalone
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        tryItOutEnabled: true,
        displayRequestDuration: true,
        docExpansion: "list",
        filter: false,
        showExtensions: true,
        showCommonExtensions: true,
        requestInterceptor: function(request) {
            // Handle CORS for localhost APIs
            if (request.url.includes('localhost') || request.url.includes('127.0.0.1')) {
                request.headers = request.headers || {};
                request.headers['Access-Control-Allow-Origin'] = '*';
            }
            return request;
        }
    });
</script>