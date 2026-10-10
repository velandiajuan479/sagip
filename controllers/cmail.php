<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once __DIR__ .'/../vendor/autoload.php';

    function envmail($correo, $titu, $mens){
        require_once("../models/clapp.php");
        $mail = new PHPMailer(true);
        
        if($correo AND $titu AND $mens){
        	try{
        		// Configurar PHPMailer
        		$mail->isSMTP();
        		$mail->SMTPAuth = true;
        		$mail->SMTPSecure = 'ssl';
        		$mail->Port = 465;
        		$mail->CharSet = "UTF-8";
        
        		//Configurar Servidor SMTP
        		$mail->Host = 'smtp.gmail.com';
        		$mail->Username = 'senacdahost@gmail.com';
        		$mail->Password = $clpp;
        
        		//Configurar mail a enviar
        		$mail->setFrom('senacdahost@gmail.com', 'Sena CDA CHIA');
        		$mail->addAddress($correo);
        		$mail->Subject = $titu;
        		$mail->isHTML(true);
        		$mail->Body = $mens;
        
        		//Enviar mail
        		$mail->Send();
        		//echo '<h2>Mensaje enviado al correo eléctronico '.$correo.' usando Gmail</h2><br><br>';
        	}catch(Exception $e){
        		//echo 'Error de envío de E-mail: '.$mail->ErrorInfo;
        		echo "<br><br>";
        	}
        }
    }
    
    function plaOlvCon($nomusu, $emausu, $keyolv){
        $txt = "";
        $txt .= "<head><meta charset='UTF-8'></head>";
        $txt .= "<body style='font-family: Arial, Verdana, Helveltica;'>";
        $txt .= "<div style='display: block;width: 90%;padding: 30px 5%;background-color: #117f09;color: #fff;font-size: 50px;font-weight: bold;height: 95px;'>";
            $txt .= "<div style='float: left;display: inline-block;'><img src='https://senacda.com/sagi/img/logoSenaB.png' width='100px'></div>";
            $txt .= "<div style='float: left;display: inline-block;margin: 20px 0px 0px 40px;text-shadow: 0px 0px 5px #fff;'>SenaCDA</div>";
        $txt .= "</div>";
        
        $txt .= "<div style='display: block;width: 100%;margin: 30px 50px;'>";
            $txt .= "Estimado/a ".$nomusu.",<br><br><br>Le escribimos para informarle que se ha solicitado un cambio de contraseña para su cuenta asociada al correo electrónico ".$emausu.".<br>Para realizar el cambio de contraseña, por favor haga clic en el siguiente botón:<br><br>";
        $txt .= "</div>";
        
        $txt .= "<form action='https://senacda.com/sagi/index.php?pg=183' method='POST' target='_blank'>";
            $txt .= "<div style='display: block;width: 100%;text-align: center;'>";
                $txt .= "<input type='submit' style='cursor: pointer;background-color: #117f09;color: #fff;padding: 15px 20px;border-radius: 15px;font-weight: bold;' value='Recuperar Contraseña'>";
                $txt .= "<input type='hidden' name='ko' value='".$keyolv."'>";
                $txt .= "<input type='hidden' name='ml' value='".$emausu."'>";
            $txt .= "</div>";
        $txt .= "</form>";
        
        $txt .= "<div style='display: block;width: 100%;margin: 30px 50px;'>";
            $txt .= "<br>Si no fue usted quien solicitó este cambio, por favor ignore este correo electrónico y no realice ninguna acción.<br><br>Agradecemos su atención y cooperación.<br><br>Atentamente,<br>Equipo de desarrollo Sena CDA.";
        $txt .= "</div>";
        
        $txt .= "<div style='display: block;width: 90%;padding: 30px 5%;background-color: #117f09;color: #fff;text-align: center;font-size: 8px;'>";
            $txt .= "...::: SAGICDA :::...<br>2773071 - Análisis y Desarrollo de de Sistemas de Información.<br>Sena Centro de Desarrollo Agroempresarial<br>CHIA - CUNDINAMARCA";
        $txt .= "</div>";
        $txt .= "</body>";
        return $txt;
    }
?>