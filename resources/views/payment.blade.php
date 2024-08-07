<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>نتیجه پرداخت</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}?v=16">

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .wrapper {
            background: #6200ed;
            height: 100%;
            direction: rtl;
            padding: 0 1rem 0 2rem;
        }
        .inner {
            width: 100%;
            max-width: 500px;
            margin: auto;
            padding: .5rem;
        }
        .card {
            margin-top: 2rem;
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
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="inner">
            <div class="card">

                <div class="icon">
                    @if($data['status'] == 1)
                        <img src="{{asset('assets/img/success.svg')}}" alt="">
                    @else
                        <img src="{{asset('assets/img/close.svg')}}" alt="">
                    @endif
                </div>

                @if($data['valid'])
                    <div class="item">
                        <div class="title">
                            <h3>وضعیت</h3>
                        </div>
                        <div class="value">
                            {{$data['status_text']}}
                        </div>
                    </div>
                    <div class="item">
                        <div class="title">
                            <h3>مقدار</h3>
                        </div>
                        <div class="value">
                            {{ number_format($data['amount']) }} تومان
                        </div>
                    </div>
                    <div class="item">
                        <div class="title">
                            <h3>علت</h3>
                        </div>
                        <div class="value">
                            {{$data['reason']}}
                        </div>
                    </div>
                    <div class="item">
                        <div class="title">
                            <h3>کد درگاه پرداخت</h3>
                        </div>
                        <div class="value">
                            {{$data['authority']}}
                        </div>
                    </div>
                    <div class="item">
                        <div class="title">
                            <h3>کد رهگیری</h3>
                        </div>
                        <div class="value">
                            @if($data['tracking_code'])
                                {{$data['tracking_code']}}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="item">
                        <div class="title">
                            <h3>تاریخ تراکنش</h3>
                        </div>
                        <div class="value">
                            {{$data['date']}}
                        </div>
                    </div>
                @else
                    <h3 style="text-align: center">
                        {{$data['message']}}
                    </h3>
                @endif

                @if(isset($data['callback']) && $data['callback'])
                    <div style="text-align: center">
                        <a class="btn btn-success" href="{{$data['callback']}}">بازگشت به لیریکفا</a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</body>
</html>
