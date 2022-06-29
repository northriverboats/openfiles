<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>North River Boats Open File Report</title>
    <!-- javascript -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=B612+Mono:wght@400;700&family=Roboto&display=swap" rel="stylesheet" />

    <!-- tailwind / daisyui -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.10.0/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="css/app.css" rel="stylesheet" type="text/css" />
  </head>
  <body style="">
    <div v-scope="{ count: 0 }">
      {{ count }}
      <button @click="count++">inc</button>
      <div class="overflow-x-auto">
      <table class="table table-compact w-full">
          <!-- head -->
          <thead>
            <tr>
              <th></th>
              <th>Share</th>
              <th>User</th>
              <th>File</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
    <script type="module" src="js/app.js"></script>
  </body>
</html>
