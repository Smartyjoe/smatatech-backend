<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('api.swagger.title', config('app.name') . ' API Documentation') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.11.0/favicon-32x32.png" sizes="32x32">
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }
        
        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }

        .swagger-ui .topbar {
            background-color: #1a1a2e;
            padding: 10px 0;
        }

        .swagger-ui .topbar .download-url-wrapper {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .swagger-ui .topbar .download-url-wrapper .download-url-button {
            background: #4a90d9;
            border-color: #4a90d9;
        }

        .swagger-ui .topbar-wrapper img {
            content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">📚</text></svg>');
            height: 40px;
        }

        .swagger-ui .topbar-wrapper::after {
            content: '{{ config('app.name', 'API') }}';
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .swagger-ui .info .title {
            color: #1a1a2e;
        }

        .swagger-ui .opblock.opblock-get {
            border-color: #61affe;
            background: rgba(97, 175, 254, 0.1);
        }

        .swagger-ui .opblock.opblock-post {
            border-color: #49cc90;
            background: rgba(73, 204, 144, 0.1);
        }

        .swagger-ui .opblock.opblock-put {
            border-color: #fca130;
            background: rgba(252, 161, 48, 0.1);
        }

        .swagger-ui .opblock.opblock-delete {
            border-color: #f93e3e;
            background: rgba(249, 62, 62, 0.1);
        }

        .swagger-ui .opblock.opblock-patch {
            border-color: #50e3c2;
            background: rgba(80, 227, 194, 0.1);
        }

        /* Version badge */
        .api-version-badge {
            position: fixed;
            top: 10px;
            right: 20px;
            z-index: 9999;
            background: #4a90d9;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Loading indicator */
        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4a90d9;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            margin-top: 20px;
            color: #666;
            font-family: sans-serif;
        }
    </style>
</head>
<body>
    <div class="api-version-badge">{{ $version ?? 'v1' }}</div>
    
    <div id="swagger-ui">
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Loading API Documentation...</p>
        </div>
    </div>

    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "{{ $openApiUrl ?? url('/api/meta/openapi') }}",
                dom_id: '#swagger-ui',
                deepLinking: {{ config('api.swagger.deep_linking', true) ? 'true' : 'false' }},
                persistAuthorization: {{ config('api.swagger.persist_authorization', true) ? 'true' : 'false' }},
                displayRequestDuration: {{ config('api.swagger.display_request_duration', true) ? 'true' : 'false' }},
                docExpansion: '{{ config('api.swagger.doc_expansion', 'list') }}',
                filter: {{ config('api.swagger.filter', true) ? 'true' : 'false' }},
                showExtensions: {{ config('api.swagger.show_extensions', true) ? 'true' : 'false' }},
                showCommonExtensions: {{ config('api.swagger.show_common_extensions', true) ? 'true' : 'false' }},
                tryItOutEnabled: {{ config('api.swagger.try_it_out_enabled', true) ? 'true' : 'false' }},
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null,
                requestInterceptor: function(request) {
                    // Add CSRF token for Laravel
                    request.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
                    return request;
                },
                onComplete: function() {
                    console.log('Swagger UI loaded successfully');
                    // Remove loading indicator styles if needed
                    document.querySelector('.loading-container')?.remove();
                }
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
