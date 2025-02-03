<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Email Verification Code</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, monospace, sans-serif;
        }
        .wrapper {
            background: #6200ed;
            height: 100%;
            padding: 1rem;
        }
        .inner {
            width: 100%;
            max-width: 500px;
            margin: auto;
        }
        .card {
            box-shadow: 0 0 10px -6px #555555;
            background: #fff;
            border-radius: 1.5rem;
            padding: 1.5rem 1rem;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .icon {
            height: 80px;
            text-align: center;
        }
        .icon img {
            height: 100%;
            width: auto;
        }
        .text-center{
            text-align: center;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="inner">
        <div class="card">
            <h1 class="text-center">Lyricfa</h1>
            <br>
            <p>Dear user, welcome to lyricfa application</p>
            <p>Your activation code:</p>
            <h2>{{$verifyCode->code}}</h2>
            <br>
            <br>
            <br>
            <p>Lyricfa, Learning English Using Music And Film</p>
        </div>
    </div>
</div>
</body>
</html>
