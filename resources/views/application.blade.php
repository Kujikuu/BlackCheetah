<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ config('app.name', 'Laravel') }} - Franchise Management System</title>
  <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
  @vite(['resources/ts/main.ts'])
</head>

<body>
  <div id="app">
    <div id="loading-bg">
      <div class="loading-logo">
        <!-- SVG Logo -->
        <svg width="68" height="68" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M26.3.5H8.7C4.2.5.5,4.2.5,8.7v17.6c0,4.5,3.7,8.2,8.2,8.2h17.6c4.5,0,8.2-3.7,8.2-8.2V8.7c0-4.5-3.7-8.2-8.2-8.2ZM30.5,25.7h-15.3c-2.4-.3-4.4-2.7-6.5-1.8-2.3.9-3.1,2-4.8,2.4h-.1v-1.3h0c2-.6,4-2.8,5.9-2.8s3.4,1.6,5,1.8c.4,0,1.6.4,1.8.4,4.1.4,8.8-.3,12.9,0,.1,0,.3-.1.3-.3,0-.6-.4-1.4-.6-1.4-1.1-.3-5.2,0-6.8,0s-4.5.1-6.8,0c-1.8-.1-4.7-1.7-6.1-1.6-1.4,0-3.7,2-5.1,2.5h-.7v-1.3h0c1.8-.9,3.4-2.3,5.1-2.6,2.8-.3,5.8,2.1,8.8,1.6-.4-.4-1.3-1.7-1.8-1.8-.4,0-5.8-1-6.2-1-1.8,0-3.8,1.6-5.8,2.7h-.1v-1.4h0c.8-.6,1.8-1,2.8-1.6,3.5-1.6,4.8-.3,8.2,0-.3-.3-.7-1.1-.9-1.3-.9-.7-4.4-.7-5.5-.6-1.4.3-3,1.3-4.5,2.1h-.3v-1.3h0c1.8-1,3.7-2.1,5.7-2.3,2,0,4.4.7,6.2.7h6.2c.3-.3-1.6-2.1-2-2.1-4.2.3-8.6-1-12.8.4-1.3.4-2.3,1-3.3,1.6h-.3v-1.3h0c.7-.4,1.4-.9,2.1-1.1,4.5-1.8,9.9-.7,14.7-.7s1.1.3,1.4.9c.4.7,1,1.6,1.6,1.6-.1-1.7-1.1-3.3-2.8-3.5-2.4-.6-9.2-.3-11.6.3-1.8.4-3.5,1.1-5.1,2h-.1v-1.3h0c2-1.1,4.1-2.1,7.2-2.4h3c.1-.1-.6-2,.9-1.8.9,0,1.4,1.6,2.1,1.8,1.7.6,3.5-.4,5.7,1,1,.7,3.1,3.8,2.4,5,0,0-1.6,1.7-1.7,1.7h-6.8c-1,0,.7,2,.9,2.3,1.1,1.4,2.1,2.4,4.1,2.6,1.4,0,8.2-.3,8.9,0h0c.4.3.7,1.7.7,3s-.1,1.4-.7,1.4h0l.6-1.1Z" fill="var(--initial-loader-color)" fill-rule="evenodd" />
        </svg>
      </div>
      <div class=" loading">
        <div class="effect-1 effects"></div>
        <div class="effect-2 effects"></div>
        <div class="effect-3 effects"></div>
      </div>
    </div>
  </div>

  <script>
    const loaderColor = localStorage.getItem('vuexy-initial-loader-bg') || '#FFFFFF'
    const primaryColor = localStorage.getItem('vuexy-initial-loader-color') || '#f05a23'

    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
    if (loaderColor)
      document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

    if (primaryColor)
      document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
  </script>
</body>

</html>
