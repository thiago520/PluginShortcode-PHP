<?php
/** 
 * Plugin Name: SaborTrivial ERP - Shortcode
 * Puglin URI: http://www.sabortrivial.com.br
 * Description: Plugin que busca o cardápio do banco de dados do sistema ERP
 * Version: 1.0
 * Author: thiago
 */
 
 include ('conexao.php'); 

 //Função que busca o cardápio do dia
 //[cardapio_dia_shortcode] 
 
 date_default_timezone_set('America/Sao_Paulo');

 function cardapioDia() {    
    
    $retornoCard = buscaCardapio(date('Y-m-d'));
    $data = date('y-m-d');


    if (date('D', strtotime($data)) == 'Sun') {      
      echo "<h2><i> Hoje é Domingo, nós não estamos atendendo!</i></h2><br><br>";
      echo "<h2><a>Confira nosso cardápio dos próximos dias...</a></h2>";
      echo "<br><br>";
     
    } else {      
         if (!$retornoCard["salada1"]) {            
            echo "<h2><i>Feriado, dia de descanso!</i></h2><br><br>";
            echo "<h2><a>Confira nosso cardápio dos próximos dias...</a></h2>";
            echo "<br><br>";
         } else {           

            foreach ((array)$retornoCard as $campo => $valor) {       
               $descLink = descricaoCardapio($valor);
               $link = "";
               if ($descLink["link_receitas"]) { 
                  $link = "<a href=" . $descLink["link_receitas"]. ">";
               }
               $descCampo = "";
               if ( $campo == "salada4" || $campo == "salada5" || $campo == "prato3" || $campo == "guarnicao3") {
                  if ($valor != "" ) {
                     $descCampo = $valor . "*";
                  } 
               } else {
                  $descCampo = $valor;
               }
               echo "<h3>". $link . $descCampo."</h3></a><i>". $descLink["descricao_receitas"]."</i>";
            }
            echo "<h3> Arroz branco, arroz integral* e feijão carioca.</h3>";
            echo "<h3 style='background-color: #CDCDCD'> * não incluídos na marmita</h3>";  
            echo "<br><br>";
         }
      }   
 } 


 function descricaoCardapio( $desc ) {    

   $conn = getConnection(); 
   $sql = "select descricao_receitas, link_receitas from receitas where nome_receitas = '$desc' ";
   $stmt = $conn->prepare($sql);
   $stmt->execute();

   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   foreach ((array) $result as $campo => $valor) {
      $descLink[$campo] = $valor;
   }

   return $descLink;
 }

 function buscaCardapio( $buscaCard ) {
   $conn = getConnection();
   $sql = "select DATE_FORMAT(data, '%d/%m/%y') as data,salada1,salada2,salada3,salada4,salada5,prato1,prato2,prato3,guarnicao1,guarnicao2,guarnicao3 from cardapio where data = '$buscaCard'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();

   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   foreach ((array) $result as $campo => $valor) {
      $resulCard[$campo] = $valor;         
   }

   return $resulCard;

 }

 //Função que busca o cardápio da semana
 //[cardapio_prox_shortcode] 

 function cardapioProx() {
       
   $dataInicial = date('Y-m-d', strtotime("+1 day"));
   $dataFinal = date('Y-m-d', strtotime("+6 days"));  
   
   while ($dataInicial <= $dataFinal) {
      $retornoCard = buscaCardapio($dataInicial);

      if (date('D', strtotime($dataInicial)) == 'Sun') {
         echo "<div id='divCardapio'>";  
         echo "<h3>Cardápio de: " . (new DateTime($dataInicial))->format('d/m/y') . "</h3>";
         echo "<h4><i>Domingo, nós não estaremos atendendo!</i></h4>";
         echo "</div>";
        
      } else {

            if ( ! $retornoCard["salada1"]) {
               echo "<div id='divCardapio'>";   
               echo "<h3>Cardápio de: " . (new DateTime($dataInicial))->format('d/m/y') . "</h3>";
               echo "<h4><i>Feriado, dia de descanso!</i></h4>";
               echo "</div>";
            } else {
               if (  $retornoCard["data"] ) {
                  echo "<div id='divCardapio'>";             
                  echo "<h3>Cardápio de: " . $retornoCard["data"] . "</h3>";
                  echo "<h4><i>" . $retornoCard["salada1"] . "</h4>";
                  echo "<h4>" . $retornoCard["salada2"] . "</h4>";
                  echo "<h4>" . $retornoCard["salada3"] . "</h4>";
                  echo "<h4>" . $retornoCard["salada4"] . "</h4>";
                  echo "<h4>" . $retornoCard["salada5"] . "</h4>";
                  echo "<h4 style='background-color: #CDCDCD'>" . $retornoCard["prato1"] . "</h4>";
                  echo "<h4 style='background-color: #CDCDCD'>" . $retornoCard["prato2"] . "</h4>";
                  echo "<h4 style='background-color: #CDCDCD'>" . $retornoCard["prato3"] . "</h4>";
                  echo "<h4>" . $retornoCard["guarnicao1"] . "</h4>";
                  echo "<h4>" . $retornoCard["guarnicao2"] . "</h4>";
                  echo "<h4>" . $retornoCard["guarnicao3"] . "</h4>";
                  echo "<h4>Arroz branco, arroz integral* e feijão carioca.</h4></i>";
                  echo "</div>";    
               }
            }         
      }             
      $dataInicial = date('Y-m-d', strtotime($dataInicial. ' + 1 day'));;
   }   
 }

//Função que busca os preços
 //[cardapio_preco_shortcode] 


function precosCardapio() {

   $nomeRefeicao = array(
      "%Alimento%peso%",
      "%Refeição%"
   );

   echo "<a><h2>Refeições:</h2></a>";

   foreach ( $nomeRefeicao as $value ) {
      $resultPreco = buscaPrecos( $value );

      if ( $resultPreco["nome_produto"] == "Alimento por peso") {
    
         echo "<div id='div_esquerda'>";
         echo "<h3 id='h3_preco'><i>&emsp; Buffet por peso (100gr)</h3></i>";
         echo "</div>";
         echo "<div id='div_direita'>";
         echo "<h3 id='h3_preco'><i>R$ " . number_format($resultPreco['preco_venda']/10, 2, ',', '.') . "</h3></i>";
         echo "</div>";
    
      }

      if ( $resultPreco["nome_produto"] == "Refeição") {
    
         echo "<div id='div_esquerda'>";
         echo "<h3 id='h3_preco'><i>&emsp; Buffet livre por pessoa</h3></i>";
         echo "<h4 id='h4_preco'><i>&emsp; Bloco de 11 tickets refeição</h4></i>";
         echo "</div>";
         echo "<div id='div_direita'>";
         echo "<h3 id='h3_preco'><i>R$ ". number_format($resultPreco['preco_venda'], 2, ',', '.') . "</h3></i>";
         echo "<h4 id='h4_preco'><i>R$ ". number_format($resultPreco['preco_venda']*10, 2, ',', '.') . "</h4></i>";
         echo "</div>";
    
      } 
   }

   $nomeMarmitas = array(
      "%Marmita%Média%",
      "%Marmita%Mini%",
      "%Marmita%Pequena%Bife%milanesa%",
      "%Marmita%Pequena%Bife%grelhado%",
      "%Marmita%Pequena%Bisteca%Porco%",
      "%Marmita%Pequena%Frango%Empanado%",
      "%Marmita%Pequena%Frango%Grelhado%",
      "%Marmita%Pequena%Tilápia%Empanada%",
      "%Marmita%Pequena%Vegetariana%"
   );

   echo "<p>&nbsp;</p>";
   echo "<a><h2>Marmitas:</h2></a>";

   foreach ($nomeMarmitas as $value ) {   
      $resultPreco = buscaPrecos( $value );

      if ($resultPreco["nome_produto"] == "Marmita Média" or $resultPreco["nome_produto"] == "Marmita Mini" ) {
      
         echo "<div id='div_esquerda'>";
         echo "<h3 id='h3_preco'><i>&emsp;" . $resultPreco["nome_produto"] . "</h3></i>";
         echo "<h4 id='h4_preco'><i>&emsp; Bloco de 11 tickets " . $resultPreco["nome_produto"] . "</h4></i>";
         echo "</div>";         
         echo "<div id='div_direita'>";
         echo "<h3 id='h3_preco'><i>R$ ". number_format($resultPreco["preco_venda"], 2, ',', '.') . "</h3></i>";         
         echo "<h4 id='h4_preco'><i>R$ ". number_format($resultPreco["preco_venda"]*10, 2, ',', '.') . "</h4></i>";         
         echo "</div>";
      
      } else {
         if ( strpos($resultPreco['nome_produto'],"milanesa") !== false ) {
      
            echo "<div id='div_esquerda'>";
            echo "<h3 id='h3_preco'><i>&emsp;" . $resultPreco["nome_produto"] . " (6ª feira)</h3></i>";
            echo "</div>";
            echo "<div id='div_direita'>";
            echo "<h3 id='h3_preco'><i>R$ ". number_format($resultPreco["preco_venda"], 2, ',', '.') . "</h3></i>";
            echo "</div>";
      
         } else {
        
            echo "<div id='div_esquerda'>";           
            echo "<h3 id='h3_preco'><i>&emsp;" . $resultPreco["nome_produto"] . "</h3></i>";  
            echo "</div>";
            echo "<div id='div_direita'>";          
            echo "<h3 id='h3_preco'><i>R$ ". number_format($resultPreco['preco_venda'], 2, ',', '.') . "</h3></i>";  
            echo "</div>";             
         }
      }
   }

   $nomePorcoes = array(
      "%Porção%anéis%cebola%empanada%",
      "%Porção%batata frita%",
      "%Porção%bife%milanesa%",
      "%Porção%bife%grelhado%",
      "%Porção%bisteca%porco%",
      "%Porção%filé%frango%empanado%",
      "%Porção%filé%frango%grelhado%"      
   );

   echo "<p>&nbsp;</p>";
   echo "<a><h2>Porções:</h2></a>";

   foreach ( $nomePorcoes as $value) {
      $resultPreco = buscaPrecos( $value );
      
      if ( strpos($resultPreco['nome_produto'],"milanesa") !== false ) {    
     
         echo "<div id='div_esquerda'>";
         echo "<h3 id='h3_preco'><i>&emsp;" . $resultPreco['nome_produto'] . " (100gr) (6ª feira)</h3></i>";
         echo "</div>";
         echo "<div id='div_direita'>";
         echo "<h3 id='h3_preco'><i>R$ "  . number_format($resultPreco['preco_venda']/10, 2, ',', '.') . "</h3></i>";
         echo "</div>";     
     } else {            
         echo "<div id='div_esquerda'>";           
         echo "<h3 id='h3_preco'><i>&emsp;" . $resultPreco['nome_produto'] . ' (100gr)</h3></i>';
         echo "</div>";
         echo "<div id='div_direita'>";          
         echo "<h3 id='h3_preco'><i>R$ ". number_format($resultPreco['preco_venda']/10, 2, ',', '.') . "</h3></i>";
         echo "</div>";   		         
      }  

   }


}


function buscaPrecos( $nomeProd ) {

   $conn = getConnection();
   $sql = "select nome_produto,preco_venda from produtos where nome_produto like '$nomeProd'";
   $stmt = $conn->prepare($sql);
   $stmt->execute();

   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   foreach ((array) $result as $campo => $valor) {
      $resulPrec[$campo] = $valor;         
   }

   return $resulPrec;

}


//Função que sorteia banner pagina inicial
 //[cardapio_banner_shortcode] 

 function banner() {
   $retornoCard = buscaCardapio(date('Y-m-d'));

   $pratos = array (
      "barreado" => array(
         1 => "barreado640200.png",
      ),

      "bifeAcebolado" => array (
         1 => "bifeacebolado640200.png",
         2 => "bifeacebolado2-640200.png",
         3 => "bifeacebolado3-640200.png",
         4 => "bifeacebolado4-640200.png",
      ),

      "bifeMilanesa" => array (
         1 => "bifemilanesa640200.png",
         2 => "bifemilanena2-640200.png",         
      ),

      "feijoada" => array (
         1 => "feijoada640200.png",
         2 => "feijoada2-640200.png",
         3 => "feijoada3-640200.png"
      ),

      "frangoEmpanado" => array (
         1 => "frangoempanado640200.png",
      ),

      "frangoPassarinho" => array (
         1 => "frangoPassarinho640200.png",
         2 => "frangoPassarinho2-640200.png",         
      ),

      "picadinho" => array (
         1 => "picadinho640200.png",
         2 => "picadinho2-640200.png",
         3 => "picadinho3-640200.png"
      ),

      "strogonoff" => array (
         1 => "strogonoff640200.png",
         2 => "strogonoff2-640200.png",
         3 => "strogonoff3-640200.png"
      ),         

   );

   $campoSorteado = array (
      "prato1",
      "prato2",
      "prato3"
   );   
   
   $sorteio = array_rand($campoSorteado, 1);    
      
   switch ( $retornoCard[$campoSorteado[$sorteio]] ) {      
         
      case "Barreado":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["barreado"][1] ."'/>";
         break;

      case "Bife acebolado":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["bifeAcebolado"][mt_rand(1,4)] ."'/>";
         break;

      case "Bife a milanesa":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["bifeMilanesa"][mt_rand(1,2)] ."'/>";
         break;

      case "Feijoada":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["feijoada"][mt_rand(1,3)] ."'/>";
         break;
      
      case "Peito de frango empanado":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["frangoEmpanado"][1] ."'/>";
         break;

      case "Coxa e sobrecoxa de frango frita":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["frangoPassarinho"][mt_rand(1,2)] ."'/>";
         break;

      case "Frango a passarinho":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["frangoPassarinho"][mt_rand(1,2)] ."'/>";
         break;
      
      case "Carne bovina com batata":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["picadinho"][mt_rand(1,3)] ."'/>";
         break;

      case "Carne bovina dos Pampas":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["picadinho"][mt_rand(1,3)] ."'/>";
         break;

      case "Carne de boi com batata e cenoura":         
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["picadinho"][mt_rand(1,3)] ."'/>";
         break;

      case "Carne bovina com legumes":         
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["picadinho"][mt_rand(1,3)] ."'/>";
         break;
      
      case "Strogonoff de carne":
         echo "<img src='./wp-content/uploads/2020/05/" . $pratos["strogonoff"][mt_rand(1,3)] ."'/>";
         break;
   }
 }

 add_shortcode('cardapio_dia_shortcode','cardapioDia'); 
 add_shortcode('cardapio_prox_shortcode','cardapioProx');
 add_shortcode('cardapio_preco_shortcode','precosCardapio');
 add_shortcode('cardapio_banner_shortcode','banner');

?>