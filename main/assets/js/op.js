function buscarPorNombres(){
    var nombresInpput =document.getElementById('nombres');
    var nombres=nombresInpput.ariaValueMax;
    ///realizar la consulta en AJAX solo si los nombres no esta vacios
    if(nombress.trim()!==''){
        //REalizar la consulta AJAX
        var xhr =new XMLHttpRequest();
        xhr.onreadystatechange= function(){
            if(xhr.readyState==4 && xhr.status==200){
                //ACTUALIZAR EL VALOR DEL CAMPO "TRABAJADOR" CON LA RESPUIESTA DEL SERVIDOR
                document.getElementById('cedula').value=xhr.responseText;
            }
        };
        xhr.open('GET','ajax.php?nombres='+nombres,true);
        xhr.send();
    }
}
document.addEventListener("DOMContentLoaded", function(){
    const nombresInpput=document.getElementById('nombres');
    const trabajadorInfo=document.getElementById("tranajadorInfo");
    nombresInpput.addEventListener("input",function(){
        const selectedNombres =nombresInpput.value;
        //REALIZAR UNA SOLICITUD AJAX PARA OBTENER LA CEDULA POR LOS NOMBRES DEL ASOCIADO
        const xhr=new XMLHttpRequest();
        xhr.onreadystatechange=function(){
            if(xhr.readyState===4&&xhr.status===200){
                //ACTYUALIZAR EL CONTENIDO DEL DIV CON LA CEDULA RECUPERADO
                trabajadorInfo.innerHTML=xhr.responseText;
            }
        };
        xhr.open("GET", "obtener_cedula.php?nombres="+selectedNombres,true)
        xhr.send();
    });

});