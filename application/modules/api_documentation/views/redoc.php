<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>API Documentation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    #redoc-container {
      height: 100vh;
    }
  </style>
</head>
<body>
  <div id="redoc-container"></div>

  <!-- Load ReDoc -->
  <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>

  <!-- Initialize ReDoc -->
  <script>
    Redoc.init(
      '<?= base_url("assets/swagger/docs/ngsi_api_documentation.json"); ?>', // CI3 dynamic URL
      {
        expandResponses: "200,400",
        scrollYOffset: 50,
        hideDownloadButton: false,
        theme: {
          typography: {
            fontSize: "14px",
            fontFamily: "Arial, sans-serif",
          },
          colors: {
            primary: { main: "#0C61A6" }
          }
        }
      },
      document.getElementById('redoc-container'),
      function(err) {
        if (err) {
          console.error("ReDoc failed to load:", err);
        }
      }
    );
  </script>
</body>
</html>
