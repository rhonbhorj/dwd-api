<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PGW API Documentation</title>
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
      
    }

    .url {
  display: none !important;
}
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
  <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-standalone-preset.js"></script>
  <script>
    window.onload = function () {
      SwaggerUIBundle({
      
        url: "<?= base_url("assets/documentation/cashout.json"); ?>",
        
        dom_id: '#swagger-ui',
        deepLinking: true,
      });
    };
  </script>
</body>
</html>