<!DOCTYPE html>
<html>
    <head>
        <title>員員努力脫離觀察期！</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">

        <link rel="stylesheet" href="css/bootstrap.min.css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;   ;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 32px;
            }

            .big {
                font-size: 96px;
            }

            .main-image {
                width: 400px;
                height: 300px;
            }

            .button-div {
                text-align: center;
                width: 400px;
                height: 50px;
            }

        </style>

    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">暫時有<span class="big">{{$count}}</span> Likes!</div>
                <div class="button-div">
                    <button class="btn btn-danger unlike-button disabled">不讚</button>
                    <input type="tel" class="like-count" value="1"></input>
                    <button class="btn btn-primary like-button disabled">讚</button>
                </div>
                <div class="main-image"><img class="main-image" src="like/image/0-gogogo.JPG"></img></div>
            </div>
        </div>
        <script src="js/jquery-2.2.0.min.js" crossorigin="anonymous"></script> 
        <script src="js/bootstrap.min.js"></script> 
                <script>
            $(document).ready(function() {
                $("button.unlike-button").toggleClass("disabled");
                $("button.like-button").toggleClass("disabled");

                $("button.unlike-button").click(function (){
                    alert("程式執行時發生故障");
                });
                $("button.like-button").click(function (){
                    var count = $("input.like-count").val();
                    window.location.href = "like/" + count;
                });
            });
        </script>
    </body>
</html>
