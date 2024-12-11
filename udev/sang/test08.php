<?
include "../lib/common.php";
include "../lib/functionDB.php";  //공통 db함수
include "../lib/functionCoupon.php";    //쿠폰관련 함수
require_once "./PHPExcel-1.8/Classes/PHPExcel.php";
require_once "./PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; 

if(isset($_FILES['upload'])){ 
	$target = "../excel/excel_data/".basename($_FILES['upload']['name']) ; 
	if(move_uploaded_file($_FILES['upload']['tmp_name'],$target)) {
		$objPHPExcel = new PHPExcel();
		// 엑셀 데이터를 담을 배열을 선언한다.
		$allData = array();
		$mem_Id = trim($memId);
		$file_name = trim(basename($_FILES['upload']['name']));
		$reg_Date = DU_TIME_YMDHIS;
		$DB_con = db1();

		//공통 폼 (맵좌표 확인)
		function common_Form($addr){
			$url = 'https://api2.sktelecom.com/tmap/pois?version=1&searchKeyword='.$addr.'&appKey=ba988557-ba1c-4617-baa6-b6668f1ce2a7';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);//헤더 정보를 보내도록 함(*필수)
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0'); 
			$contents = curl_exec($ch); 
			$contents_json = json_decode($contents, true); // 결과값을 파싱
			curl_close($ch);
	//		return $contents_json['searchPoiInfo']['pois']['poi']['0'];	// 최종 위치정보만 가져오기
			return $contents_json;
		}
		function common_Form2($addr){
			$url = 'https://naveropenapi.apigw.ntruss.com/map-geocode/v2/geocode?query='.$addr.'&X-NCP-APIGW-API-KEY-ID=hiznu965vx&X-NCP-APIGW-API-KEY=2adILXyRAWoc3jpyonesul3e2BDv1A5vHEQPCzwX';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);//헤더 정보를 보내도록 함(*필수)
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0'); 
			$contents = curl_exec($ch); 
			$contents_json = json_decode($contents, true); // 결과값을 파싱
			curl_close($ch);
	//		return $contents_json['searchPoiInfo']['pois']['poi']['0'];
			return $contents_json;
		}
		$chkquery = "
			SELECT idx
			FROM TB_MEMBERS
			WHERE mem_Id = :mem_Id
				AND b_Disply = 'N' ;" ;
		$chkstmt = $DB_con->prepare($chkquery);
		$chkstmt->bindparam(":mem_Id",$mem_Id);
		$chkstmt->execute();	
		$chkrow = $chkstmt->fetch(PDO::FETCH_ASSOC);
		$chknum = $chkstmt->rowCount();
		$mIdx = $chkrow['idx'];

		$chklistquery = "
				SELECT idx
				FROM TB_EXCEL_LIST
				WHERE mem_Id = :mem_Id
					AND f_Name = :f_Name ;" ;
		$chkliststmt = $DB_con->prepare($chklistquery);
		$chkliststmt->bindparam(":mem_Id",$mem_Id);
		$chkliststmt->bindparam(":f_Name",$file_name);
		$chkliststmt->execute();	
		$chklistnum = $chkliststmt->rowCount();
		if($chklistnum < 1){
			$listquery = "
					INSERT INTO TB_EXCEL_LIST (mem_Id, mem_Idx, f_Name, u_Date, reg_Date)
					VALUES (:mem_Id, :mem_Idx, :f_Name, :u_Date, :reg_Date)" ;
			$liststmt = $DB_con->prepare($listquery);
			$liststmt->bindparam(":mem_Id",$mem_Id);
			$liststmt->bindparam(":mem_Idx",$mIdx);
			$liststmt->bindparam(":f_Name",$file_name);
			$liststmt->bindparam(":u_Date",$reg_Date);
			$liststmt->bindparam(":reg_Date",$reg_Date);
			$liststmt->execute();
			// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
			$objReader = PHPExcel_IOFactory::createReaderForFile("./excel_data/".$file_name);
			// 읽기전용으로 설정
			$objReader->setReadDataOnly(true);
			// 엑셀파일을 읽는다
			$objExcel = $objReader->load("./excel_data/".$file_name);
			// 첫번째 시트를 선택
			$objExcel->setActiveSheetIndex(0);
			$objWorksheet = $objExcel->getActiveSheet();
			$rowIterator = $objWorksheet->getRowIterator();
			foreach ($rowIterator as $row) { // 모든 행에 대해서
					   $cellIterator = $row->getCellIterator();
					   $cellIterator->setIterateOnlyExistingCells(false); 
			}
			$maxRow = $objWorksheet->getHighestRow();
			if((int)$maxRow != 0){
			}
			for ($i = 1 ; $i <= $maxRow; $i++) {
				if($i == 1){
					continue;
				}
				$e_Date = $objWorksheet->getCell('A' . $i)->getValue();
				$e_Time = $objWorksheet->getCell('B' . $i)->getValue();
				$e_State = $objWorksheet->getCell('C' . $i)->getValue();
				$Manager = $objWorksheet->getCell('D' . $i)->getValue();
				$p_Date = $objWorksheet->getCell('E' . $i)->getValue();
				$p_Manager = $objWorksheet->getCell('F' . $i)->getValue();
				$p_Time = $objWorksheet->getCell('G' . $i)->getValue();
				$c_No = $objWorksheet->getCell('H' . $i)->getValue();
				$c_Name = $objWorksheet->getCell('I' . $i)->getValue();
				$car_No = $objWorksheet->getCell('J' . $i)->getValue();
				$p_Name = $objWorksheet->getCell('K' . $i)->getValue();
				$b_No = $objWorksheet->getCell('L' . $i)->getValue();
				$d_Distance = $objWorksheet->getCell('M' . $i)->getValue();
				$c_Distance = $objWorksheet->getCell('N' . $i)->getValue();
				$r_Date = $objWorksheet->getCell('O' . $i)->getValue();
				$rDate = PHPExcel_Style_NumberFormat::toFormattedString($r_Date, 'YYYY-MM-DD'); // 날짜 형태의 셀을 읽을때는 toFormattedString를 사용한다.
				$p_Tel = $objWorksheet->getCell('P' . $i)->getValue();
				$h_Tel = $objWorksheet->getCell('Q' . $i)->getValue();
				$z_Code = $objWorksheet->getCell('R' . $i)->getValue();
				$n_Addr = $objWorksheet->getCell('S' . $i)->getValue();
				$p_Addr = $objWorksheet->getCell('T' . $i)->getValue();
				$d_Addr = $objWorksheet->getCell('U' . $i)->getValue();
				$o_State = $objWorksheet->getCell('V' . $i)->getValue();
				if($e_State == "초기배정"){
					$e_State = "0";
				}else{
					$e_State = "1";	//점검완료
				}
				$address = $n_Addr; 
				$addr = urlencode($address);
				$res = common_Form($addr);
				if(is_array($res['error']) == 1 || $res == '') {						//주소 값이 에러인 경우
					$res2 = common_Form2($addr);
					if($res2['status'] == 'OK'){
						$fLng = $res2['addresses']['0']['x'];		//경도
						if($fLng ==''){
							$Lng = '';
						}else{
							$Lng = $fLng;
						}
						$fLat = $res2['addresses']['0']['y'];		//위도
						if($fLat ==''){
							$Lat = '';
						}else{
							$Lat = $fLat;
						}
					}else{
						$Lng = '0.000000000000000';
						$Lat = '0.000000000000000';
					}
				} else {													//주소 값이 정상인 경우
					$fLng = $res['searchPoiInfo']['pois']['poi']['0']['frontLon'];		//경도
					if($fLng ==''){
						$Lng = '';
					}else{
						$Lng = $fLng;
					}
					$fLat = $res['searchPoiInfo']['pois']['poi']['0']['frontLat'];		//위도
					if($fLat ==''){
						$Lat = '';
					}else{
						$Lat = $fLat;
					}
				}
				$query = "
						INSERT INTO TB_EXCEL_DATA (mem_Id, mem_Idx, e_Date, e_Time, e_State, Manager, p_Date, p_Manager, p_Time, c_No, c_Name, car_No, p_Name, b_No, d_Distance, c_Distance, r_Date, p_Tel, h_Tel, z_Code, n_Addr, p_Addr, d_Addr, Lng, Lat, o_State, f_Name, reg_Date)
						VALUES (:mem_Id, :mem_Idx, :e_Date, :e_Time, :e_State, :Manager, :p_Date, :p_Manager, :p_Time, :c_No, :c_Name, :car_No, :p_Name, :b_No, :d_Distance, :c_Distance, :r_Date, :p_Tel, :h_Tel, :z_Code, :n_Addr, :p_Addr, :d_Addr, :Lng, :Lat, :o_State, :f_Name, :reg_Date)" ;
				$stmt = $DB_con->prepare($query);
				$stmt->bindparam(":mem_Id",$mem_Id);
				$stmt->bindparam(":mem_Idx",$mIdx);
				$stmt->bindparam(":e_Date",$e_Date);
				$stmt->bindparam(":e_Time",$e_Time);
				$stmt->bindparam(":e_State",$e_State);
				$stmt->bindparam(":Manager",$Manager);
				$stmt->bindparam(":p_Date",$p_Date);
				$stmt->bindparam(":p_Manager",$p_Manager);
				$stmt->bindparam(":p_Time",$p_Time);
				$stmt->bindparam(":c_No",$c_No);
				$stmt->bindparam(":c_Name",$c_Name);
				$stmt->bindparam(":car_No",$car_No);
				$stmt->bindparam(":p_Name",$p_Name);
				$stmt->bindparam(":b_No",$b_No);
				$stmt->bindparam(":d_Distance",$d_Distance);
				$stmt->bindparam(":c_Distance",$c_Distance);
				$stmt->bindparam(":r_Date",$rDate);
				$stmt->bindparam(":p_Tel",$p_Tel);
				$stmt->bindparam(":h_Tel",$h_Tel);
				$stmt->bindparam(":z_Code",$z_Code);
				$stmt->bindparam(":n_Addr",$n_Addr);
				$stmt->bindparam(":p_Addr",$p_Addr);
				$stmt->bindparam(":d_Addr",$d_Addr);
				$stmt->bindparam(":Lng",$Lng);		//경도
				$stmt->bindparam(":Lat",$Lat);		//위도
				$stmt->bindparam(":o_State",$o_State);
				$stmt->bindparam(":f_Name",$file_name);
				$stmt->bindparam(":reg_Date",$reg_Date);
				$stmt->execute();
				ignore_user_abort(true);
				set_time_limit(0);
				ini_set('memory_limit','1024M');
				//, "data" =>$allData
				// $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
			  }
			  $result = array("result" => "success");
			  echo json_encode($result);
			
		}else{
			$result = array("result" => "error", "errorMsg" => "이미 업로드한 엑셀파일입니다. 확인 후 다시 시도해주세요.");
			echo json_encode($result);
		}	
	}else{
		$result = array("result" => "error1");
		echo json_encode($result);
	}
}else{ 
	$result = array("result" => "error2");
	echo json_encode($result);
} 
dbClose($DB_con);
$liststmt = null;
$chkliststmt = null;
$chkstmt = null;
$stmt = null;
?>