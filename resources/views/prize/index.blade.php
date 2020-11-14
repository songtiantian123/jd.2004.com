<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <title>抽奖</title>

</head>

<body>
<h1>抽奖</h1>
<button id="btn-prize">开始抽奖</button>
</body>
</html>
<script src="/js/jquery-3.2.1.min.js"></script>
<script>
    $(document).ready(function(){
        // 抽奖
        $(document).on("click","#btn-prize",function(){
            var _this = $(this);
            $.ajax({
                url:"/prize/start",
                type:"get",
                dataType:"json",
                success:function(d){
                    //alert(d.data.prize);
                    if(d.error == 200){
                        alert(d.data.level);
                    }else if(d.error == 2004){
                        window.location.href="/user/login";
                    }
                }
            });
        })
    })

</script>

