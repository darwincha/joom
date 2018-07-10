<?php 
require_once('conexion.php'); 
$con = mysqli_connect($hostname_conexion,$username_conexion,$password_conexion,$database_conexion);
mysqli_set_charset($con, "utf8");        
$database = mysqli_select_db($con,$database_conexion);
$httphost = filter_input(INPUT_SERVER, 'HTTP_HOST');
?>
<html>
<head>	
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <!--*************** Ventanas Modales**********************-->
  <link rel="stylesheet" type="text/css" href="/servicios/css/ventanas-modales.css">
  <script type="text/javascript" src="/servicios/code/jquery-1.8.0.min.js"></script>
  <script type="text/javascript" src="/servicios/code/ventanas-modales.js"></script>   
  <!--**************************************************************-->
  <link rel="stylesheet" href="/servicios/code/jquery-ui.css">
  <script src="/servicios/code/jquery-ui.js"></script>
  <script>
  $(function() {
    $( "#tabs" ).tabs();
  });
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content"
    });
  });
  $(function() {
    $( "#accordion1" ).accordion({
      heightStyle: "content"
    });
  });
  </script>
  <link rel="stylesheet" href="/servicios/css/style.css">
  <link rel="stylesheet" href="/servicios/css/style_responsive.css">	
</head>
<body>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">WFS por Sector</a></li>
        <li><a href="#tabs-2">WFS por Clasificación</a></li>
    </ul>
<?php
	echo "<div id='tabs-1'><div id='accordion1'>";	
	$sqlSector = "SELECT DISTINCT poder.podvnombre "
	. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.ge_poder poder "
	. "WHERE (poder.podnid=entidad.podnid)and(entidad.entnid=respuesta.entnid)and(respuesta.pk_id_rsta=rstadetalle.pk_id_rsta)and(rstadetalle.pk_id_pregtip='2')";	        
    $resultSector = mysqli_query($con,$sqlSector);
 	while ($row = mysqli_fetch_row($resultSector))                
	{
		$sector=$row[0];        
        $sqlListaSector = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,clasificacion.vc_clasific_categoria,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END,rstadetalle.vc_rsta_outputformat "
    	. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion, idepcoor.ge_poder poder "
    	. "WHERE (respuesta.entnid=entidad.entnid)and(rstadetalle.pk_id_rsta=respuesta.pk_id_rsta)and(rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(poder.podnid=entidad.podnid)and(rstadetalle.pk_id_pregtip='2')and(poder.podvnombre='$sector')";                   
		$resultListaSector = mysqli_query($con,$sqlListaSector); 
		$tiposoutput = array();
		$select;
		$i;		
		echo "<h3>$sector</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Tema</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Descargar</th></tr></thead><tbody>"; 
        while ($row = mysqli_fetch_row($resultListaSector))      	
        {			
			$tiposoutput=  explode(",", $row[6]);
			$select="<option value='0'>Elegir formato de descarga</option>";
			$i =0;
			foreach($tiposoutput as $tipo){
				if($i>0)
					$select=$select."<option value='".$row[4]."&outputFormat=".$tipo."'>".$tipo."</option>\n";
				$i++;
			}						
			$urlwfs = explode("?", $row[4]);			
			$item=base64_encode("WFS".$urlwfs[0]."?service=WFS&version=2.0.0&request=GetCapabilities");					
			if($row[5]=='activo.png'){$title="El servicio está operativo";}else{$title="El servicio está temporalmente inactivo";};						
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td><img title='".$title."' src='/images/".$row[5]."'></td>";						
            echo "<td><div id='divContenido'><a href='/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";
			echo "<td><select onChange='download(this.value)'>".$select."</select></td></tr>";    	
        }
            echo "</tbody></table></div> ";
	}
	echo "</div></div>"; 	
  	echo "<div id='tabs-2'><div id='accordion'>";        
	$sqlClasificacion = "SELECT DISTINCT clasificacion.vc_clasific_categoria FROM idepcoor.gepr_ta_clasific clasificacion, idepcoor.gepr_ta_rstadetalle rstadetalle "
	. "WHERE (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(rstadetalle.pk_id_pregtip='2')";         
	$resultClasificacion = mysqli_query($con,$sqlClasificacion);                  
 	while ($row = mysqli_fetch_row($resultClasificacion))            
  	{
		$clasificacion=$row[0];
		$sqlListaClasif = "SELECT rstadetalle.vc_rstadet_nombre,rstadetalle.vc_rstadet_descripcion,entidad.entvnombre,rstadetalle.vc_rstadet_direcurl,CASE WHEN b_direcurl_estado='1' THEN 'activo.png' ELSE 'inactivo.png' END,rstadetalle.vc_rsta_outputformat "
    	. "FROM idepcoor.gen_entidad entidad, idepcoor.gepr_ta_respuesta respuesta, idepcoor.gepr_ta_rstadetalle rstadetalle, idepcoor.gepr_ta_clasific clasificacion, idepcoor.ge_poder poder "
    	. "WHERE (respuesta.entnid=entidad.entnid) and (rstadetalle.pk_id_rsta=respuesta.pk_id_rsta) and (rstadetalle.pk_id_clasific=clasificacion.pk_id_clasific)and(poder.podnid=entidad.podnid)and(rstadetalle.pk_id_pregtip='2')and(clasificacion.vc_clasific_categoria='$clasificacion')"; 
		$resultListaClasif = mysqli_query($con,$sqlListaClasif); 
		$tiposoutput = array();
		$select;
		$i;			
		echo "<h3>$clasificacion</h3><div><table><thead><tr><th>Nombre del Servicio</th><th>Descripción</th><th>Proporcionado por</th><th>Estado</th><th>Dirección del Servicio</th><th>Descargar</th></tr></thead><tbody>";        		          
		while ($row = mysqli_fetch_row($resultListaClasif)) 		            
        {			
			$tiposoutput=  explode(",", $row[5]);
			$select="<option value='0'>Elegir formato de descarga</option>";
			$i =0;
			foreach($tiposoutput as $tipo){
				if($i>0)
					$select=$select."<option value='".$row[3]."&outputFormat=".$tipo."'>".$tipo."</option>\n";
				$i++;
			}								
			$urlwfs = explode("?", $row[3]);			
			$item=base64_encode("WFS".$urlwfs[0]."?service=WFS&version=2.0.0&request=GetCapabilities");								
			if($row[4]=='activo.png'){$title="El servicio está operativo";}else{$title="El servicio está temporalmente inactivo";};			
			echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td><img title='".$title."' src='/images/".$row[4]."'></td>"; 			
			echo "<td><div id='divContenido'><a href='/servicios/modalservicio.php?serv=$item' class='clsVentanaIFrame clsBoton' rel='Detalle de Servicio' on>Ver dirección</a></div></td>";			
			//echo "<td><a href='http://".$httphost."/visor?servicio=". base64_encode($row[3])."' target='_blank' title='WFS : ".$row[3]."' >Descargar</a></td></tr>";    
			echo "<td><select onChange='download(this.value)'>".$select."</select></td></tr>";    
        }              
        echo "</tbody></table></div> ";
	}
	echo "</div></div>"; 
?>    
</div>
<p class='home-idep-texto'>Revisión de estado de los servicios el día <?php echo date('d-m-Y'); ?> a las 12:00 a.m.</p>
<script>
function download(d) {
        if (d == 'Select document') return;
		if(d!="0")
		window.open(d,'_blank');
}
</script>
</body>
</html>
