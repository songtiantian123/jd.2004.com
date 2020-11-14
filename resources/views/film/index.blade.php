<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>电影订票</title>
</head>
<body>
<h1>电影订票系统</h1>
<form action="{{url('/film/filmadd')}}" method="post">
    <table border="1">
        <input type="hidden" name="film_id" value="{{$_GET['film_id']}}">
        @csrf
        @foreach($film_count as $k=>$v)
            @if($k % 5 ==0)
                <tr></tr>
            @endif
                <td>
                    {{$v['seat_num']}}
                    @if(!in_array($v['seat_num'],$seat_num))
                    <input type="checkbox" name="film_count[]" value="{{$v['seat_num']}}" >
                    @else
                      x
                    @endif
                </td>
        @endforeach
    </table>
    <input type="reset" value="重置">
    <button id="btn-film">一键购票</button>
    <p>注：</p>
    <p>&nbsp;&nbsp;x为当前座位号已被购买</p>
</form>
</body>
</html>
<script src="/js/jquery-3.2.1.min.js"></script>
<script>
    $(document).ready(function(){
        // TODO 一键购票
        $(document).on("click",'#btn-film',function(){
            var film_count = $(":checkbox");
            console.log(film_count);
            film_count.each(function(){
                var _this = $(this);
                if(_this.prop('checked')==true){
                    console.log(_this.val());
                }
            })
        })
    })
</script>






