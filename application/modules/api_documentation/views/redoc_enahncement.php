<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">

  <title>API Documentation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="shortcut icon" href="<?= base_url("assets/img/ngsi.png"); ?>">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    /* Logo header bar */
    .api-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      background-color: #f5f7fa;
      border-bottom: 1px solid #ddd;
    }

    .api-header img {
      height: 40px;
    }

    .api-header h1 {
      font-size: 20px;
      color: #0C61A6;
      margin: 0;
    }

    #redoc-container {
      height: calc(100vh - 60px); /* subtract header height */
    }
  </style>
</head>
<body>
  <!-- Custom logo header -->
  <div class="api-header">
    <img src="<?= base_url("assets/img/ngsi.png"); ?>" alt="API Logo">
    <h1>NGSI API Documentation</h1>
  </div>

  <!-- ReDoc container -->
  <div id="redoc-container"></div>

  <!-- Load ReDoc -->
  <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>

  <!-- Initialize ReDoc -->
  <script>
    Redoc.init(
      '<?= base_url("assets/swagger/docs/ngsi_api_documentation.json"); ?>',
      {
        expandResponses: "200,400",
        scrollYOffset: 60, // match header height
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
        if (err) console.error("ReDoc failed to load:", err);
      }
    );
  </script>
</body>
</html>
