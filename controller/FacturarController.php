<?php
class FacturarController extends ControladorBase{
    
    public function __construct() {
        parent::__construct();
    }

      
		public function index(){

	
		session_start();
		if (isset(  $_SESSION['id_usuarios']) )
		{
			
			$factura_cabeza = new FacturaCabezaModel();
			$factura_detalle = new FacturaDetalleModel();
			
			$tipo_pago = new TipoPagoModel();
			$resultTipPago = $tipo_pago->getAll("nombre_tipo_pago");
			
			
			$nombre_controladores = "Facturar";
			$id_rol= $_SESSION['id_rol'];
			$resultPer = $factura_cabeza->getPermisosVer("controladores.nombre_controladores = '$nombre_controladores' AND permisos_rol.id_rol = '$id_rol' " );
				
			if (!empty($resultPer))
			{
					
					$this->view("Facturar",array(
							"resultTipPago" =>$resultTipPago
					
					));
				
			}
			else
			{
				$this->view("Error",array(
						"resultado"=>"No tiene Permisos de Acceso a Facturar"
			
				));
			
			}
			
		
		}
		else{
       	
       	$this->redirect("Usuarios","sesion_caducada");
       	
       }
		
	}
	
	
	
	
	
	public function consulta_productos()
	{
		 
		session_start();
		$id_rol=$_SESSION["id_rol"];
		 
		$productos = null; $productos = new ProductosModel();
		$where_to="";
		$columnas = "productos.id_productos,
                      productos.codigo_productos,
                      productos.nombre_productos,
                      productos.precio_productos";
		 
		$tablas = " public.productos";
		 
		$where    = "productos.id_estado=1";
		 
		$id       = "productos.id_productos";
		 
		 
		$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
		$search =  (isset($_REQUEST['search'])&& $_REQUEST['search'] !=NULL)?$_REQUEST['search']:'';
		 
		 
		if($action == 'ajax')
		{
			 
			if(!empty($search)){
				 
				$where1=" AND (productos.nombre_productos LIKE '".$search."%' OR productos.codigo_productos LIKE '".$search."%')";
				 
				$where_to=$where.$where1;
				 
			}else{
				 
				$where_to=$where;
				 
			}
			 
			 
			$html="";
			$resultSet=$productos->getCantidad("*", $tablas, $where_to);
			$cantidadResult=(int)$resultSet[0]->total;
			 
			$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
			 
			$per_page = 10; //la cantidad de registros que desea mostrar
			$adjacents  = 9; //brecha entre páginas después de varios adyacentes
			$offset = ($page - 1) * $per_page;
			 
			$limit = " LIMIT   '$per_page' OFFSET '$offset'";
			 
			$resultSet=$productos->getCondicionesPag($columnas, $tablas, $where_to, $id, $limit);
			$count_query   = $cantidadResult;
			$total_pages = ceil($cantidadResult/$per_page);
			 
			 
			if($cantidadResult>0)
			{
				 
				$html.='<div class="pull-left" style="margin-left:15px;">';
				$html.='<span class="form-control"><strong>Registros: </strong>'.$cantidadResult.'</span>';
				$html.='<input type="hidden" value="'.$cantidadResult.'" id="total_query" name="total_query"/>' ;
				$html.='</div>';
				$html.='<div class="col-lg-12 col-md-12 col-xs-12">';
				$html.='<section style="height:300px; overflow-y:scroll;">';
				$html.= "<table id='tabla_productos' class='tablesorter table table-striped table-bordered dt-responsive nowrap dataTables-example'>";
				$html.= "<thead>";
				$html.= "<tr>";
				$html.='<th style="text-align: left;  font-size: 12px;"></th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Codigo</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Nombre</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Cantidad</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Precio U.</th>';
				$html.='<th style="text-align: left;  font-size: 12px;"></th>';
				 
				$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody >';
				 
				 
				$i=0;
				 
				foreach ($resultSet as $res)
				{
					$i++;
					$html.='<tr>';
					$html.='<td style="font-size: 11px;">'.$i.'</td>';
					$html.='<td style="font-size: 11px;">'.$res->codigo_productos.'</td>';
					$html.='<td style="font-size: 11px;">'.$res->nombre_productos.'</td>';
					$html.='<td class="col-xs-1"><div class="pull-right">';
					$html.='<input type="number" class="form-control input-sm"  id="cantidad_'.$res->id_productos.'" value="1"></div></td>';
					$html.='<td class="col-xs-2"><div class="pull-right">';
					$html.='<input type="text" class="form-control input-sm"  id="pecio_producto_'.$res->id_productos.'" value="'.$res->precio_productos.'" readonly></div></td>';
					$html.='<td style="font-size: 18px;"><span class="pull-right"><a href="#" onclick="agregar_producto('.$res->id_productos.')" class="btn btn-info" style="font-size:65%;"><i class="glyphicon glyphicon-plus"></i></a></span></td>';
					 
					$html.='</tr>';
				}
				$html.='</tbody>';
				 
				
				$html.='</table>';
				$html.='</section></div>';
				$html.='<div class="table-pagination pull-right">';
				$html.=''. $this->paginatemultiple("index.php", $page, $total_pages, $adjacents,"load_productos").'';
				$html.='</div>';
				
				
				 
			}else{
				$html.='<div class="col-lg-12 col-md-12 col-xs-12">';
				$html.='<div class="alert alert-warning alert-dismissable" style="margin-top:40px;">';
				$html.='<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
				$html.='<h4>Aviso!!!</h4> <b>Actualmente no hay productos registrados...</b>';
				$html.='</div>';
				$html.='</div>';
			}
			 
			 
			echo $html;
			 
		}
		 
	}
	
	
	

	public function paginatemultiple($reload, $page, $tpages, $adjacents,$funcion='') {
		 
		$prevlabel = "&lsaquo; Prev";
		$nextlabel = "Next &rsaquo;";
		$out = '<ul class="pagination pagination-large">';
		 
		// previous label
		 
		if($page==1) {
			$out.= "<li class='disabled'><span><a>$prevlabel</a></span></li>";
		} else if($page==2) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='$funcion(1)'>$prevlabel</a></span></li>";
		}else {
			$out.= "<li><span><a href='javascript:void(0);' onclick='$funcion(".($page-1).")'>$prevlabel</a></span></li>";
			 
		}
		 
		// first label
		if($page>($adjacents+1)) {
			$out.= "<li><a href='javascript:void(0);' onclick='$funcion(1)'>1</a></li>";
		}
		// interval
		if($page>($adjacents+2)) {
			$out.= "<li><a>...</a></li>";
		}
		 
		// pages
		 
		$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
		$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
		for($i=$pmin; $i<=$pmax; $i++) {
			if($i==$page) {
				$out.= "<li class='active'><a>$i</a></li>";
			}else if($i==1) {
				$out.= "<li><a href='javascript:void(0);' onclick='$funcion(1)'>$i</a></li>";
			}else {
				$out.= "<li><a href='javascript:void(0);' onclick='$funcion(".$i.")'>$i</a></li>";
			}
		}
		 
		// interval
		 
		if($page<($tpages-$adjacents-1)) {
			$out.= "<li><a>...</a></li>";
		}
		 
		// last
		 
		if($page<($tpages-$adjacents)) {
			$out.= "<li><a href='javascript:void(0);' onclick='$funcion($tpages)'>$tpages</a></li>";
		}
		 
		// next
		 
		if($page<$tpages) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='$funcion(".($page+1).")'>$nextlabel</a></span></li>";
		}else {
			$out.= "<li class='disabled'><span><a>$nextlabel</a></span></li>";
		}
		 
		$out.= "</ul>";
		return $out;
	}
	
	
	
	
	public function insertaDetalleFactura(){
		 
		session_start();
		 
		$_id_usuarios = $_SESSION['id_usuarios'];
		 
		$producto_id = (isset($_REQUEST['id_productos'])&& $_REQUEST['id_productos'] !=NULL)?$_REQUEST['id_productos']:0;
		 
		$cantidad = (isset($_REQUEST['cantidad'])&& $_REQUEST['cantidad'] !=NULL)?$_REQUEST['cantidad']:0;
		 
		$precio_unitario = (isset($_REQUEST['precio_u'])&& $_REQUEST['precio_u'] !=NULL)?$_REQUEST['precio_u']:0;
		 
		 
		if($_id_usuarios!='' && $producto_id>0){
			 
			
			$total = $precio_unitario*$cantidad;
			
			$tempFactura = new TempFacturaModel();
			
			$funcion = "ins_temp_factura";
			$parametros = "
			'$producto_id',
			'$cantidad',
			'$precio_unitario',
			'$total',
			'$_id_usuarios'";
			 
			$tempFactura->setFuncion($funcion);
			$tempFactura->setParametros($parametros);
			 
			$resultado=$tempFactura->llamafuncion();
			 
			$respuesta = 0;
			 
			if(!empty($resultado) && count($resultado)>0){
				 
				foreach ($resultado[0] as $k=>$v){
					$respuesta=$v;
				}
			}
			 
			echo  json_encode(array('mensaje'=>$respuesta));
			 
		}
		 
	}
	
	
	


	public function eliminaTempFactura(){
		 
		session_start();
		 
		$_id_usuarios = $_SESSION['id_usuarios'];
		 
		$id_temp_factura = (isset($_REQUEST['id_temp_factura'])&& $_REQUEST['id_temp_factura'] !=NULL)?$_REQUEST['id_temp_factura']:0;
		 
		if($_id_usuarios!='' && $id_temp_factura>0){
			 
			$_session_id = session_id();
			
			$temp_factura = new TempFacturaModel();
			 
			$where = "id_usuarios = $_id_usuarios AND id_temp_factura = $id_temp_factura ";
			$resultado=$temp_factura->deleteById($where);
			 
			$this->trae_temporal($_id_usuarios);
		}
	}
	
	
	
	
	public function trae_temporal($id_usuario = null){
		 
		 
		$page =  (isset($_REQUEST['page'])&& $_REQUEST['page'] !=NULL)?$_REQUEST['page']:1;
		 
		$id_usuario =  isset($_SESSION['id_usuarios'])?$_SESSION['id_usuarios']:null;
		 
		if($id_usuario==null){ session_start(); $id_usuario=$_SESSION['id_usuarios'];}
		
		$_iva = isset($_POST['iva']) ? $_POST['iva'] : 0.12;
		$_descuento = isset($_POST['descuento']) ? $_POST['descuento'] : 0.00;
		 
	    $subtotal = 0.00;
		 
		if($id_usuario != null)
		{
			
			$iva = new IvaModel();
			$resultIva = $iva->getAll("id_iva");
				
			$descuento = new DescuentoModel();
			$resultDesc = $descuento->getAll("id_descuento");
				
			 
			$temp_factura = new TempFacturaModel();
			 
			$col_temp="temp_factura.id_temp_factura, 
					  productos.id_productos, 
					  productos.nombre_productos, 
					  productos.codigo_productos, 
					  temp_factura.cantidad_temp_factura, 
					  temp_factura.precio_unitario_temp_factura, 
					  temp_factura.total_temp_factura, 
					  temp_factura.id_usuarios, 
					  temp_factura.creado";
			 
			$tab_temp = "public.temp_factura, 
  						 public.productos";
			 
			$where_temp = "productos.id_productos = temp_factura.id_productos AND temp_factura.id_usuarios='$id_usuario'";
			 
			 
			$resultSet=$temp_factura->getCantidad("*", $tab_temp, $where_temp);
			$cantidadResult=(int)$resultSet[0]->total;
			 
			$per_page = 15; //la cantidad de registros que desea mostrar
			$adjacents  = 9; //brecha entre páginas después de varios adyacentes
			$offset = ($page - 1) * $per_page;
			 
			$limit = " LIMIT   '$per_page' OFFSET '$offset'";
			 
			$resultSet=$temp_factura->getCondicionesPag($col_temp, $tab_temp, $where_temp, "temp_factura.id_temp_factura", $limit);
			$count_query   = $cantidadResult;
			$total_pages = ceil($cantidadResult/$per_page);
			 
			$html="";
			if($cantidadResult>0)
			{
				 
				$html.='<div class="pull-left" style="margin-left:11px;">';
				$html.='<span class="form-control"><strong>Registros: </strong>'.$cantidadResult.'</span>';
				$html.='<input type="hidden" value="'.$cantidadResult.'" id="total_query_compras" name="total_query"/>' ;
				$html.='</div>';
				$html.='<div class="col-lg-12 col-md-12 col-xs-12">';
				$html.='<section style="height:400px; overflow-y:scroll;">';
				$html.= "<table id='tabla_temporal' class='tablesorter table table-striped table-bordered dt-responsive nowrap'>";
				$html.= "<thead>";
				$html.= "<tr>";
				$html.='<th style="text-align: left;  font-size: 12px;">Codigo</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Nombre</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">Cantidad</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">P. Unitario</th>';
				$html.='<th style="text-align: left;  font-size: 12px;">P. Total</th>';
				$html.='<th style="text-align: left;  font-size: 12px;"></th>';
				 
				$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';
				 
				$i=0;
			    $valor_total_db=0; $valor_total_vista=0;
			    $subtotal = 0.00;
				 
				foreach ($resultSet as $res)
				{
					
					
					
					$i++;
					$html.='<tr>';
					$html.='<td style="font-size: 11px;">'.$res->codigo_productos.'</td>';
					$html.='<td style="font-size: 11px;">'.$res->nombre_productos.'</td>';
					$html.='<td style="font-size: 11px;">'.$res->cantidad_temp_factura.'</td>';
					$html.='<td style="font-size: 11px; text-align:right; ">'.$res->precio_unitario_temp_factura.'</td>';
					$html.='<td style="font-size: 11px; text-align:right; ">'.$res->total_temp_factura.'</td>';
					$html.='<td style="font-size: 18px;"><span class="pull-right"><a href="#" onclick="eliminar_temporal('.$res->id_temp_factura.')" class="btn btn-danger" style="font-size:65%;"><i class="glyphicon glyphicon-trash"></i></a></span></td>';
					 
					$html.='</tr>';
					
					$subtotal +=$res->total_temp_factura;
					
				}
				 
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1 style="margin-right:15px;"><b>SubTotal&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>';
				$html.='<td class="text-right" style="font-size: 12px;">'.number_format((float)$subtotal, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1>';
				$html.='<div class="form-group" style="text-align: right;">';
				$html.='<label for="iva" class="col-sm-9 control-label" style="text-align: right;"><b>Iva</b></label>';
				$html.='<div class="col-sm-3">';
				$html.='<select name="iva" id="iva" class="form-control" style="text-align: right; height:30px;">';
				foreach($resultIva as $res) {
				    if($res->porcentaje_iva == $_iva){
				        $html.='<option value="'.$res->porcentaje_iva.'" selected >'.$res->nombre_iva.'</option>';
				    }else{
				        $html.='<option value="'.$res->porcentaje_iva.'" >'.$res->nombre_iva.'</option>';
				    }
				    
				}
				$valorconiva = 0.00;
				$valorconiva = $_iva * $subtotal;
				$html.='</select>';
				$html.='</div>';
				$html.='</div>';
				$html.='</td>';
				$html.='<td class="text-right" style="font-size: 12px;">'.number_format((float)$valorconiva, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1>';
				$html.='<div class="form-group" style="text-align: right;">';
				$html.='<label for="descuento" class="col-sm-9 control-label" style="text-align: right;"><b>Descuento</b></label>';
				$html.='<div class="col-sm-3">';
				$html.='<select name="descuento" id="descuento" class="form-control" style="text-align: right; height:30px;">';
				foreach($resultDesc as $res) {
				    if($res->porcentaje_descuento == $_descuento){
				        $html.='<option value="'.$res->porcentaje_descuento.'" selected >'.$res->nombre_descuento.'</option>';
				    }else{
				        $html.='<option value="'.$res->porcentaje_descuento.'" >'.$res->nombre_descuento.'</option>';
				    }
				   
				}
				$valorcondescuento = 0.00;
				$valorcondescuento = $_descuento * $subtotal;
				$html.='</select>';
				$html.='</div>';
				$html.='</div>';
				$html.='</td>';
				$html.='<td class="text-right" style="font-size: 12px;">'.number_format((float)$valorcondescuento, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				$valortotalFactura = 0.00;
				$valortotalFactura = $subtotal+$valorconiva-$valorcondescuento;
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1><b>TOTAL $&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>';
				$html.='<td id="trtotalFactura" class="text-right" style="font-size: 12px;">'.number_format((float)$valortotalFactura, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				
				
				
				
				
				
				$html.='</tbody>';
				$html.='</table>';
				$html.='</section></div>';
				$html.='<div class="table-pagination pull-right">';
				$html.=''. $this->paginatemultiple("index.php", $page, $total_pages, $adjacents,"loadDetalleFactura").'';
				$html.='</div>';
				 
				 
				 
			}else{
				
				
				
				$html.='<div class="pull-left" style="margin-left:11px;">';
				$html.='<span class="form-control"><strong>Registros: </strong>'.$cantidadResult.'</span>';
				$html.='<input type="hidden" value="'.$cantidadResult.'" id="total_query_compras" name="total_query"/>' ;
				$html.='</div>';
				$html.='<div class="col-lg-12 col-md-12 col-xs-12">';
				$html.='<section style="height:400px; overflow-y:scroll;">';
				$html.= "<table id='tabla_temporal' class='tablesorter table table-striped table-bordered dt-responsive nowrap'>";
				$html.= "<thead>";
				$html.= "<tr>";
				$html.='<th style="text-align: left;  font-size: 13px;">Codigo</th>';
				$html.='<th style="text-align: left;  font-size: 13px;">Nombre</th>';
				$html.='<th style="text-align: left;  font-size: 13px;">Cantidad</th>';
				$html.='<th style="text-align: left;  font-size: 13px;">P. Unitario</th>';
				$html.='<th style="text-align: left;  font-size: 13px;">P. Total</th>';
				$html.='<th style="text-align: left;  font-size: 13px;"></th>';
					
				$html.='</tr>';
				$html.='</thead>';
				$html.='<tbody>';
				
				
				
				$html.='<tr>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 18px;"></td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 18px;"></td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 18px;"></td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 18px;"></td>';
				$html.='</tr>';
				
				$html.='<tr>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 11px;"></td>';
				$html.='<td style="font-size: 18px;"></td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1 style="margin-right:15px;"><b>SubTotal&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>';
				$html.='<td class="text-right" style="font-size: 12px;">0.00</td>';
				$html.='</tr>';
				
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1>';
				$html.='<div class="form-group" style="text-align: right;">';
				$html.='<label for="iva" class="col-sm-9 control-label" style="text-align: right;"><b>Iva</b></label>';
				$html.='<div class="col-sm-3">';
				$html.='<select name="iva" id="iva" class="form-control" style="text-align: right; height:30px;">';
				foreach($resultIva as $res) {
				    if($res->porcentaje_iva == $_iva){
				        $html.='<option value="'.$res->porcentaje_iva.'" selected >'.$res->nombre_iva.'</option>';
				    }else{
				        $html.='<option value="'.$res->porcentaje_iva.'" >'.$res->nombre_iva.'</option>';
				    }
				}
				$valorconiva = 0.00;
				$valorconiva = $_iva * $subtotal;
				$html.='</select>';
				$html.='</div>';
				$html.='</div>';
				$html.='</td>';
				$html.='<td class="text-right" style="font-size: 12px;">'.number_format((float)$valorconiva, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1>';
				$html.='<div class="form-group" style="text-align: right;">';
				$html.='<label for="descuento" class="col-sm-9 control-label" style="text-align: right;"><b>Descuento</b></label>';
				$html.='<div class="col-sm-3">';
				$html.='<select name="descuento" id="descuento" class="form-control" style="text-align: right; height:30px;">';
				foreach($resultDesc as $res) {
				    if($res->porcentaje_descuento == $_descuento){
				        $html.='<option value="'.$res->porcentaje_descuento.'" selected >'.$res->nombre_descuento.'</option>';
				    }else{
				        $html.='<option value="'.$res->porcentaje_descuento.'" >'.$res->nombre_descuento.'</option>';
				    }
				}
				
				$valorcondescuento = 0.00;
				$valorcondescuento = $_descuento * $subtotal;
				$html.='</select>';
				$html.='</div>';
				$html.='</div>';
				$html.='</td>';
				$html.='<td class="text-right" style="font-size: 12px;">'.number_format((float)$valorcondescuento, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				$valortotalFactura = 0.00;
				$valortotalFactura = $subtotal+$valorconiva-$valorcondescuento;
				
				$html.='<tr>';
				$html.='<td class="text-right" colspan=3></td>';
				$html.='<td class="text-right" colspan=1><b>TOTAL $&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>';
				$html.='<td id="trtotalFactura" class="text-right" style="font-size: 12px;">'.number_format((float)$valortotalFactura, 2, ',', '.').'</td>';
				$html.='</tr>';
				
				
				$html.='</tbody>';
				$html.='</table>';
				$html.='</section></div>';
				
			}
			 
			 
			echo $html;
			 
		}
		 
		 
	}
	/**
	 * mod: compras
	 * title: resultados_temp
	 * ajax: si
	 * fn_ajax carga_resultados_temp
	 *
	 */
	public function resultados_temp(){
		 
		session_start();
		 
		$id_usuario = (isset($_SESSION['id_usuarios']))?$_SESSION['id_usuarios']:0;
		 
		if($id_usuario>0){
			 
			$_session_id = session_id();
			 
			//para eliminado de temp
			$temp_compras = new TempComprasModel();
			 
			$sql_query = "SELECT SUM(total_temp_compras) as \"subtotal12\" ,0.00 as \"subtotal0\",
                        SUM(total_temp_compras) AS \"subtotal\", 0.00 AS \"descuento\",
                        TRUNC(sum(total_temp_compras)* 0.12,2) AS \"iva\"";
			 
			$sql_query.=" FROM public.temp_compras";
	
			$sql_query .= " WHERE id_usuarios = $id_usuario ";
			 
			$resultado=$temp_compras->enviaquery($sql_query);
			 
			//print_r($resultado);
			 
			$htmlsubtotales="";
			if(!empty($resultado)){
				if(is_array($resultado)){
					if(count($resultado)>0){
						 
						$clasecolumnas='class="col-lg-2 col-md-2"';
						$claseinput = 'class="form-control"';
						foreach ($resultado as $res){
							 
							$htmlsubtotales = '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_subtotal12" class="control-label">Subtotal 12:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_subtotal12" id="rs_subtotal12" type="text" value="'.$res->subtotal12.'" readonly/>';
							$htmlsubtotales .= '</div>';
							$htmlsubtotales .= '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_subtotal0" class="control-label">Subtotal 0:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_subtotal0" id="rs_subtotal0" type="text" value="'.$res->subtotal0.'" readonly />';
							$htmlsubtotales .= '</div>';
							$htmlsubtotales .= '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_subtotal" class="control-label">Subtotal:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_subtotal" id="rs_subtotal" type="text" value="'.$res->subtotal.'" readonly />';
							$htmlsubtotales .= '</div>';
							$htmlsubtotales .= '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_descuento" class="control-label">Descuento:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_descuento" id="rs_descuento" type="text" value="'.$res->descuento.'" readonly />';
							$htmlsubtotales .= '</div>';
							$htmlsubtotales .= '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_iva" class="control-label">I.V.A 12:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_iva" id="rs_iva" type="text" value="'.$res->iva.'"  readonly />';
							$htmlsubtotales .= '</div>';
							$htmlsubtotales .= '<div '.$clasecolumnas.'>';
							$htmlsubtotales .= '<label for="rs_total" class="control-label">Total:</label>';
							$htmlsubtotales .= '<input '.$claseinput.' name="rs_total" id="rs_total" type="text" value="'.($res->subtotal+$res->iva).'" readonly />';
							$htmlsubtotales .= '</div>';
						}
						 
						 
						//echo json_encode($resultado);
						 
					}
				}
				 
				echo $htmlsubtotales;
			}
		}
		 
	}
	
	/***
	 * mod: compras,
	 * title: para cancelar la accion de compras
	 * return: retorna otra vista
	 */
	public function cancelarcompra(){
		 
		session_start();
		 
		$id_usuario = (isset($_SESSION['id_usuarios']))?$_SESSION['id_usuarios']:0;
		 
		if($id_usuario>0){
			 
			$_session_id = session_id();
			 
			//para eliminado de temp
			$temp_compras = new TempComprasModel();
			 
			$where = "id_usuarios = $id_usuario ";
			$resultado=$temp_compras->deleteById($where);
			 
			$this->redirect("MovimientosInv","compras");
		}
	}
	
	/**
	 * mod:compras
	 * title: para isertar compras
	 * retrun: json de respuesta
	 */
	
	public function inserta_compras(){
		 
		session_start();
		 
		$id_usuarios = (isset($_SESSION['id_usuarios']))?$_SESSION['id_usuarios']:0;
		$id_rol = (isset($_SESSION['id_rol']))?$_SESSION['id_rol']:0;
		 
		$movimientosInvCabeza = new MovimientosInvCabezaModel();
		 
		/*valores de la vista*/
		$_numero_compra = (isset($_POST['numero_compra']))?$_POST['numero_compra']:'';
		$_fecha_compra = (isset($_POST['fecha_compra']))?$_POST['fecha_compra']:'';
		$_cantidad_compra = (isset($_POST['cantidad_compra']))?$_POST['cantidad_compra']:'';
		$_importe_compra = (isset($_POST['importe_compra']))?$_POST['importe_compra']:'';
		$_numero_factura_compra = (isset($_POST['numero_factura_compra']))?$_POST['numero_factura_compra']:'';
		$_numero_autorizacion_compra = (isset($_POST['numero_autorizacion_factura']))?$_POST['numero_autorizacion_factura']:'';
		$_subtotal_12_compra = (isset($_POST['subtotal_12_compra']))?$_POST['subtotal_12_compra']:'';
		$_subtotal_0_compra = (isset($_POST['subtotal_0_compra']))?$_POST['subtotal_0_compra']:'';
		$_iva_compra = (isset($_POST['iva_compra']))?$_POST['iva_compra']:'';
		$_descuento_compra = (isset($_POST['descuento_compra']))?$_POST['descuento_compra']:'';
		$_estado_compra = (isset($_POST['estado_compra']))?$_POST['estado_compra']:0;
		 
		//$id_rol = (isset($_SESSION['id_rol']))?$_SESSION['id_rol']:0;
		 
		// se valida por cantidad si tiene en la tabla temp_compras
		if($_cantidad_compra>0){
			 
			 
			 
		}
		 
		/*raise*/
		//id consecutivo consultar ?
		$_id_consecutivo = 0;
		//numero movimiento consultar ?
		$_numero_movimiento = 0;
		 
		/*para variables de la funcion*/
		$razon_movimientos="compra de productos";
		 
		$funcion = "fn_agrega_compra";
		$parametros = "'$id_usuarios','$_id_consecutivo','$_numero_compra','$razon_movimientos',
		'$_fecha_compra', '$_cantidad_compra','$_importe_compra','$_numero_factura_compra',
		'$_numero_autorizacion_compra','$_subtotal_12_compra','$_subtotal_0_compra',
		'$_iva_compra','$_descuento_compra','$_estado_compra'";
		 
		$movimientosInvCabeza->setFuncion($funcion);
		$movimientosInvCabeza->setParametros($parametros);
		$resultset = $movimientosInvCabeza->llamafuncion();
		 
	
		 
		 
		 
		print_r($resultset);
		 
		if(!empty($resultset)){
			echo "es array";
		}else{
			echo "no es array";
		}
		 
	}
	
	
	
	
	
	


	public function AutocompleteCedula(){
			
		session_start();
		$_id_usuarios= $_SESSION['id_usuarios'];
		$clientes = new ClientesModel();
		$identificacion_clientes = $_GET['term'];
			
		$resultSet=$clientes->getBy("identificacion_clientes LIKE '$identificacion_clientes%' AND id_estado=1");
			
		if(!empty($resultSet)){
	
			foreach ($resultSet as $res){
					
				$_identificacion_clientes[] = $res->identificacion_clientes;
			}
			echo json_encode($_identificacion_clientes);
		}
			
	}
	
	
	
	
	
	public function AutocompleteDevuelveNombres(){
			
		session_start();
		$_id_usuarios= $_SESSION['id_usuarios'];
		$clientes = new ClientesModel();
			
		$identificacion_clientes = $_POST['identificacion_clientes'];
		$resultSet=$clientes->getBy("identificacion_clientes = '$identificacion_clientes' AND id_estado=1");
			
		$respuesta = new stdClass();
			
		if(!empty($resultSet)){
	
			$respuesta->id_clientes = $resultSet[0]->id_clientes;
			$respuesta->razon_social_clientes = $resultSet[0]->razon_social_clientes;
		    $respuesta->identificacion_clientes = $resultSet[0]->identificacion_clientes;
			$respuesta->celular_clientes = $resultSet[0]->celular_clientes;
			$respuesta->correo_clientes = $resultSet[0]->correo_clientes;
			
			
			echo json_encode($respuesta);
		}
			
	}
	
	
	
	


	public function AutocompleteNombre(){
			
		session_start();
		$clientes = new ClientesModel();
		$razon_social_clientes = $_GET['term'];
			
		$resultSet=$clientes->getBy("razon_social_clientes LIKE '$razon_social_clientes%' AND id_estado=1");
			
		if(!empty($resultSet)){
	
			foreach ($resultSet as $res){
					
				$_razon_social_clientes[] = $res->razon_social_clientes;
			}
			echo json_encode($_razon_social_clientes);
		}
			
	}
	
	
	
	
	
	public function AutocompleteDevuelveCedula(){
			
		session_start();
		$clientes = new ClientesModel();
			
		$razon_social_clientes = $_POST['razon_social_clientes'];
		$resultSet=$clientes->getBy("razon_social_clientes = '$razon_social_clientes' AND id_estado=1");
			
		$respuesta = new stdClass();
			
		if(!empty($resultSet)){
	
			$respuesta->razon_social_clientes = $resultSet[0]->razon_social_clientes;
			$respuesta->identificacion_clientes = $resultSet[0]->identificacion_clientes;
			$respuesta->celular_clientes = $resultSet[0]->celular_clientes;
			$respuesta->correo_clientes = $resultSet[0]->correo_clientes;
			$respuesta->id_clientes = $resultSet[0]->id_clientes;
				
				
			echo json_encode($respuesta);
		}
			
	}
	
	
	

	
	
	
	public function paginate_clientes_activos($reload, $page, $tpages, $adjacents) {
	
		$prevlabel = "&lsaquo; Prev";
		$nextlabel = "Next &rsaquo;";
		$out = '<ul class="pagination pagination-large">';
	
		// previous label
	
		if($page==1) {
			$out.= "<li class='disabled'><span><a>$prevlabel</a></span></li>";
		} else if($page==2) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_activos(1)'>$prevlabel</a></span></li>";
		}else {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_activos(".($page-1).")'>$prevlabel</a></span></li>";
	
		}
	
		// first label
		if($page>($adjacents+1)) {
			$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_activos(1)'>1</a></li>";
		}
		// interval
		if($page>($adjacents+2)) {
			$out.= "<li><a>...</a></li>";
		}
	
		// pages
	
		$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
		$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
		for($i=$pmin; $i<=$pmax; $i++) {
			if($i==$page) {
				$out.= "<li class='active'><a>$i</a></li>";
			}else if($i==1) {
				$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_activos(1)'>$i</a></li>";
			}else {
				$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_activos(".$i.")'>$i</a></li>";
			}
		}
	
		// interval
	
		if($page<($tpages-$adjacents-1)) {
			$out.= "<li><a>...</a></li>";
		}
	
		// last
	
		if($page<($tpages-$adjacents)) {
			$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_activos($tpages)'>$tpages</a></li>";
		}
	
		// next
	
		if($page<$tpages) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_activos(".($page+1).")'>$nextlabel</a></span></li>";
		}else {
			$out.= "<li class='disabled'><span><a>$nextlabel</a></span></li>";
		}
	
		$out.= "</ul>";
		return $out;
	}
	
	
	
	
	

	public function paginate_clientes_inactivos($reload, $page, $tpages, $adjacents) {
	
		$prevlabel = "&lsaquo; Prev";
		$nextlabel = "Next &rsaquo;";
		$out = '<ul class="pagination pagination-large">';
	
		// previous label
	
		if($page==1) {
			$out.= "<li class='disabled'><span><a>$prevlabel</a></span></li>";
		} else if($page==2) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_inactivos(1)'>$prevlabel</a></span></li>";
		}else {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_inactivos(".($page-1).")'>$prevlabel</a></span></li>";
	
		}
	
		// first label
		if($page>($adjacents+1)) {
			$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_inactivos(1)'>1</a></li>";
		}
		// interval
		if($page>($adjacents+2)) {
			$out.= "<li><a>...</a></li>";
		}
	
		// pages
	
		$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
		$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
		for($i=$pmin; $i<=$pmax; $i++) {
			if($i==$page) {
				$out.= "<li class='active'><a>$i</a></li>";
			}else if($i==1) {
				$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_inactivos(1)'>$i</a></li>";
			}else {
				$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_inactivos(".$i.")'>$i</a></li>";
			}
		}
	
		// interval
	
		if($page<($tpages-$adjacents-1)) {
			$out.= "<li><a>...</a></li>";
		}
	
		// last
	
		if($page<($tpages-$adjacents)) {
			$out.= "<li><a href='javascript:void(0);' onclick='load_clientes_inactivos($tpages)'>$tpages</a></li>";
		}
	
		// next
	
		if($page<$tpages) {
			$out.= "<li><span><a href='javascript:void(0);' onclick='load_clientes_inactivos(".($page+1).")'>$nextlabel</a></span></li>";
		}else {
			$out.= "<li class='disabled'><span><a>$nextlabel</a></span></li>";
		}
	
		$out.= "</ul>";
		return $out;
	}
	
	public function InsertaFactura(){
	   	    
	    $_id_clientes = $_POST['id_clientes'];
	    $_id_usuarios = $_POST['id_usuarios'];
	    $_id_tipo_pago = $_POST['id_tipo_pago'];
	    $_fecha_factura = $_POST['fecha_factura_cabeza'];
	    $_porcentaje_iva = $_POST['porcentaje_iva'];
	    $_porcentaje_descuento = $_POST['porcentaje_descuento'];	    
	    $_plazo_pago = $_POST['plazo_pago'];
	    $_numero_factura = $_POST['numero_factura'];
	    
	    $respuesta = array();
	    
	    $Factura = new FacturaCabezaModel();
	    
	    try {
	        
	        //validar tipo de pago 
	        $nombreTipoPago = "";
	        $queryTipoPago = "SELECT * FROM public.tipo_pago WHERE id_tipo_pago = $_id_tipo_pago LIMIT 1";
	        $rsTipoPago = $Factura->enviaquery($queryTipoPago);
	        if(!empty($rsTipoPago)){
	            $nombreTipoPago = $rsTipoPago[0]->nombre_tipo_pago;
	            $nombreTipoPago = strtoupper($nombreTipoPago);
	        }
	        
	        //validacion de numero de factura
	        $queryNumeroFactura = "select 1 from factura_cabeza where numero_factura_cabeza = '$_numero_factura'";
	        $rsValidaNumero= $Factura->enviaquery($queryNumeroFactura);
	        if(!empty($rsTipoPago)){
	           
	            echo '<message>Numero de Factura ya se encuentra registrado<message>';
	            die();
	        }
	        
	        if( $nombreTipoPago == 'EFECTIVO' ){
	            
	            $funcion = "ins_factura";
	            $parametros = "$_id_usuarios,$_id_clientes,$_id_tipo_pago,'$_fecha_factura','$_porcentaje_iva','$_porcentaje_descuento',$_numero_factura";
	            $Factura->setFuncion($funcion);
	            $Factura->setParametros($parametros);
	            
	            $resultado = $Factura -> llamafuncionPG();
	            
	            if(is_null($resultado))
	                throw new Exception( "error al insertar Factura");
	                
                if ( $resultado[0] > 0 ){
                    
                    $respuesta['mensaje'] = "Factura Ingresada correctamente";
                    $respuesta['valor'] = 1;
                    $respuesta['id_factura'] = $resultado[0];
                    
                }
                echo json_encode($respuesta); die();
                
	        }elseif($nombreTipoPago == "CREDITO"){
	            
	            $funcion = "ins_factura";
	            $parametros = "$_id_usuarios,$_id_clientes,$_id_tipo_pago,'$_fecha_factura','$_porcentaje_iva','$_porcentaje_descuento',$_numero_factura";
	            $Factura->setFuncion($funcion);
	            $Factura->setParametros($parametros);
	            
	            $resultado = $Factura -> llamafuncionPG();
	            
	            if(is_null($resultado))
	                throw new Exception( "error al insertar Factura");
	            
	                
                if ( (int)$resultado[0] > 0 ){
                    
                    $_id_factura = $resultado[0];
                    
                    $respuesta['mensaje'] = "Factura Ingresada correctamente ";
                    $respuesta['valor'] = 1;
                    $respuesta['id_factura'] = $resultado[0];
                    
                }
                echo json_encode($respuesta); die();
	        }
	        
	        echo "<message>Validacion de datos fallida<message>";
	        
	    } catch (Exception $e) {
	        
	        echo "<message>error generando cheque". $e->getMessage()."<message>";
	    }
	    	  
	}
	
	
	public function devuelveNumFactura(){
	    
	    $respuesta = array();
	    
	    $columnas = "lpad(valor_consecutivos::text,espacio_consecutivos,'0') numero_factura";
	    $tablas = "public.consecutivos";
	    $where =" nombre_consecutivos = 'FACTURA'";
	    $id = "id_consecutivos";
	    
	    $Factura = new FacturaCabezaModel();
	    
	    $resultado = $Factura->getCondiciones($columnas,$tablas,$where,$id);
	    
	    $respuesta['valor'] = $resultado[0]->numero_factura;
	    
	    echo json_encode($respuesta);
	}	
	
	public function tablaAmortizacion($_valor_capital = 1000, $numero_cuotas = 12, $interes_mensual = 0 , $fecha_corte = '2019-07-21')
	{
	    
	    $monto = 8990.00;
	    $tasa = 0.00; //en porcentaje %
	    $plazo = 66;
	    
	    /*para operaciones*/
	    $resultAmortizacion = array();
	    $fila = array();
	    $cuota = 0;
	    $saldoIni = 0.0;
	    $saldoFinal = 0.00;
	    $amortizacion = $monto/$plazo;
	    $saldoIni = $monto;
	    $interes = 0.0;
	    $pago = 0.00;
	    $saldoFinal = $saldoIni;
	    
	    $totalINt = 0;
	    $totalCAP = 0;
	    
	    for($i = 0; $i < $plazo; $i++){
	        $fila = array();
	        $cuota = $i+1;
	        $interes = $saldoFinal*($tasa/100);
	        $saldoFinal = $saldoIni-$amortizacion;
	        $pago = $interes + $amortizacion;
	        $totalINt +=  round($interes,2);
	        $totalCAP += round($pago,2);
	        $fila['cuota'] = $cuota;
	        $fila['fecha'] = 'Mes-'.$cuota;
	        $fila['saldo_inicial'] = round($saldoIni,2);
	        $fila['interes'] = round($interes,2);
	        $fila['amortizacion'] = round($amortizacion,2);
	        $fila['pago'] = round($pago,2);
	        $fila['saldo_final'] = round($saldoFinal,2);
	        
	        $saldoIni=$saldoFinal;
	        
	        array_push($resultAmortizacion, $fila);
	    }
	    
	    
	    $diferencia = round($totalCAP - $monto - $totalINt,2); 
	    
	    $resultAmortizacion[0]['amortizacion'] = ($resultAmortizacion[0]['amortizacion']) + $diferencia;
	    
	    
	    $htmlTabla = '<table border="1">';
	    
	    $htmlTabla.='<tr>';
	    $htmlTabla.='<td>Mes</td>';
	    $htmlTabla.='<td>Fecha</td>';
	    $htmlTabla.='<td>Saldo Inicial</td>';
	    $htmlTabla.='<td>Interes|</td>';
	    $htmlTabla.='<td>Amortizacion</td>';
	    $htmlTabla.='<td>Pago</td>';
	    $htmlTabla.='<td>Saldo Actual</td>';
	    $htmlTabla.='</tr>';
	    
	    foreach ($resultAmortizacion as $res)
	    {
	        $htmlTabla.='<tr>';
	        $htmlTabla.='<td>'.$res['cuota'].'</td>';
	        $htmlTabla.='<td>'.$res['fecha'].'</td>';
	        $htmlTabla.='<td>'.$res['saldo_inicial'].'</td>';
	        $htmlTabla.='<td>'.$res['interes'].'</td>';
	        $htmlTabla.='<td>'.$res['amortizacion'].'</td>';
	        $htmlTabla.='<td>'.$res['pago'].'</td>';
	        $htmlTabla.='<td>'.$res['saldo_final'].'</td>';
	        $htmlTabla.='</tr>';
	        
	      
	        
	    }
	    $htmlTabla .= '</table>';
	    echo $htmlTabla; echo '<br>'. $totalINt .'<br>'.$totalCAP ; die();
	    
	   
	    $num=$totalCAP-$totalINt;
	    if($i==1)
	    {
	     $pago=$pago+$num;   
	    }
	    for($i = 0; $i < $plazo; $i++){
	        
	        $cuota = $i+1;
	        $interes = $saldoFinal*($tasa/100);
	        $saldoFinal = $saldoIni-$amortizacion;
	        $pago = $interes + $amortizacion;
	        $totalINt +=  round($interes,2);
	        $totalCAP += round($pago,2);
	        
	        $htmlTabla.='<tr>';
	        $htmlTabla.='<td>'.$cuota.'</td>';
	        $htmlTabla.='<td>Mes-'.$cuota.'</td>';
	        $htmlTabla.='<td>'.round($saldoIni,2).'</td>';
	        $htmlTabla.='<td>'.round($interes,2).'</td>';
	        $htmlTabla.='<td>'.round($amortizacion,2).'</td>';
	        $htmlTabla.='<td>'.round($pago,2).'</td>';
	        $htmlTabla.='<td>'.round($saldoFinal,2).'</td>';
	        $htmlTabla.='</tr>';
	        
	        $saldoIni=$saldoFinal;
	    }
	    

	}
	
	
	
	
}
?>
