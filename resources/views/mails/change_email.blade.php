<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body{
            font-family:Arial, Helvetica, sans-serif
        }

        .button{

            background:fuchsia;
            padding: 10px 15px 10px 15px;
            color: white !important;
            text-decoration: none;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            border-radius:5px;
            font-size: 20px;
        }

        .button:hover{

            background:rgb(219, 30, 219);

        }
      </style>
</head>

<body>
    <br>
    <br>
    <div class="container2">

        <div class="card" style="border:1px rgb(217, 217, 217) solid;box-shadow: 3px 3px 3px rgb(213, 212, 211);padding: 40px;border-radius: 10px;max-width: 700px;">
            <div style="text-align:center">
                <!-- <img style="width: 250px;" src="
                    {{-- {{$message->embed(asset('public/images/default/LogoEskaDentalcompleto.png'))}} --}}
                    " class="logo-mail" style="" data-auto-embed="attachment"/>
               <img class="logo-mail" src="{!! asset('public\images\default\Logo Eska Dental completo.png') !!}" alt="">  -->
            <br>
            <h2 style="color:grey">Cambio de correo de tu cuenta OnlyFetixx</h2>
            <br>
            <h3 style=""> Tu codigo de confirmacion de cambio de correo es: </h3>


            <h1 style="border:dotted 1px grey;width:200px;background:rgb(235, 234, 234);margin:auto"> {{$change_email["token"]}} </h1>
            <br>
            <p style="color:grey" >Este codigo sera obsoleto despues de una 2 horas desu generación. Si usted no desea cambiar el correo de su cuenta onlyfetixx, o es ajeno a esta operación, solo ignore este mensaje. </p>
            <br>
            <p class="" style="text-align:center;line-height:1;font-size:14px;color:grey">
               No responder este mensaje
            </p>
        </div>
        </div>

    </div>
</body>
</html>
