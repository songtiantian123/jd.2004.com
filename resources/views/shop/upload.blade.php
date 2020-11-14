<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>显示图片</title>
</head>
<body>
    <form action="/shop/uploadImg1" method="post" enctype="multipart/form-data">
       @csrf
        <input type="file" name="img">
        <input type="submit" value="上传">
    </form>
    <!--第一种图片路径-->
    <!-- <img src="/storage/img/aaa.jpg" alt="" />-->
    <!--第二种图片路径 App_URL根据 .env中可以变-->
    <img src="{{ env('APP_URL')}}/storage/img/aaa.jpg " alt="" >
</body>
</html>
