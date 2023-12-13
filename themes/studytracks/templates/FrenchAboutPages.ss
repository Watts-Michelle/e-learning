<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href="{$BaseHref}">
    <title>Document</title>

    <style>
        html {
            min-height: 100%;
        }
        body {
            background: #685ECB; /* For browsers that do not support gradients */
            background: -webkit-linear-gradient(-170deg, rgba(104, 94, 203, 100), rgba(81, 152, 154, 100), rgba(76, 154, 185, 100)); /* For Safari 5.1 to 6.0 */
            background: -o-linear-gradient(-170deg, rgba(104, 94, 203, 100), rgba(81, 152, 154, 100), rgba(76, 154, 185, 100)); /* For Opera 11.1 to 12.0 */
            background: -moz-linear-gradient(-170deg, rgba(104, 94, 203, 100), rgba(81, 152, 154, 100), rgba(76, 154, 185, 100)); /* For Firefox 3.6 to 15 */
            background: linear-gradient(-170deg, rgba(104, 94, 203, 100), rgba(81, 152, 154, 100), rgba(76, 154, 185, 100)); /* Standard syntax */
            background-repeat: no-repeat;
            min-height: 100%;
            color: #fff;
            font-family: "Helvetica Neue", Helvetica, arial;
        }

        .container {
            padding: 1em;
            color: #fff;
        }

        h1 {
            font-size: 30px;
            font-weight: 400;
            margin: 0;
            padding: 0;
        }

        p {
            font-size: 16px;
            line-height: 1.5em;
            font-weight: 400;
        }

        header {
            text-align: center;
            padding: 50px 0;
        }
        header .logo {
            max-width: 60%;
        }

        a {
            color: #fff;
            font-weight: 600;
            text-decoration: underline;
        }

        a:hover {
            text-decoration: none;
        }

        img {
            border: 0;
            max-width: 100%;
        }

        .table-block h2 {
            background-color: rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            font-size: 24px;
            line-height: 2.2em;
            font-weight: 400;
            margin: 0;
        }
        table {
            max-width: 100%;
            border: 0;
        }
        table thead tr th {
            text-transform: uppercase;
            background-color: rgba(255, 255, 255, 0.2);
            line-height: 3em;
            text-align: center;
            width: 50%;
        }

        table tbody tr td {
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding: 10px 5px;
            font-size: 14px;
        }

        iframe {
            max-width: 100%;
            height: auto;
        }

    </style>
</head>
<body>
<header>
    <img src="{$BaseHref}/{$ThemeDir}/images/logo.png" alt="" class="logo">
</header>


<div class="container">
    <h1>$Title</h1>
</div>
<hr>
<div class="container">
    $Content.RAW
</div>


</body>
</html>