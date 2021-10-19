<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'PTAstra';
            src: url({{ storage_path('fonts/PT Astra Serif_Regular.ttf') }}) format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'PTAstra';
            src: url({{ storage_path('fonts/PT Astra Serif_Italic.ttf') }}) format("truetype");
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: 'PTAstra';
            src: url({{ storage_path('fonts/PT Astra Serif_Bold.ttf') }}) format("truetype");
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'PTAstra';
            src: url({{ storage_path('fonts/PT Astra Serif_Bold Italic.ttf') }}) format("truetype");
            font-weight: bold;
            font-style: italic;
        }
        body {
            margin: 1cm 1cm 1cm 2cm;
        }
        * {
            font: normal 12pt "PTAstra", serif;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
        }
        .td-border td {
            border: 2px #000000 solid;
            padding: 5px 15px;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .justify {
            text-align: justify;
        }
        .bold {
            font-weight: bold;
        }
        .field-desc {
            border-top: 2px #000000 solid;
            font-size: 10pt !important;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
