<?
/*
* 프로그램				: 지도정보조회
* 페이지 설명			: 지도의 정보를 조회할 수 있다.
* 파일명					: info_detail_contents.php
* 관련DB					: TB_CONTENTS
*/
include "../lib/common.php";
include "../lib/functionDB.php";
$DB_con = db1();
$mem_Id = trim($memId);						//회원아이디
if(memChk($mem_Id) == "0"){
	$mem_Id = "GUEST";							//회원아이디가 없는 경우 비회원으로 처리
}else{
	$mIdx = memIdxInfo($mem_Id);			// 회원고유번호
}
$con_Idx = trim($conIdx);						//지도고유번호
$con_chk_Bit = contentsChk($con_Idx);		// 지도고유번호 확인 
$con_reg_Id = contentsIdInfo($con_Idx);	// 지도등록아이디 확인
//$reg_Date = DU_TIME_YMDHIS;				//등록일

if ($con_Idx != "") {
	if ($con_chk_Bit == "1") {						// 지도삭제여부 체크
		$data = [];
		if($con_reg_Id != $mem_Id){
			//지도 기본테이블 조회
			$query = "
				SELECT idx, con_Name, con_Lv, category, img, tag, thumbnail_Bit, like_Cnt, kml_File, memo, reg_Id, reg_Date, end_Date
				FROM TB_CONTENTS
				WHERE idx = :idx
					AND open_Bit = '0'
					AND delete_Bit = '0'
				GROUP BY idx
				ORDER BY reg_Date DESC;
			";
		}else{
			//지도 기본테이블 조회
			$query = "
				SELECT idx, con_Name, con_Lv, category, img, tag, thumbnail_Bit, like_Cnt, kml_File, memo, reg_Id, reg_Date, end_Date
				FROM TB_CONTENTS
				WHERE idx = :idx
					AND delete_Bit = '0'
				GROUP BY idx
				ORDER BY reg_Date DESC;
			";
		}
		$stmt = $DB_con->prepare($query);
		$stmt->bindParam(":idx", $con_Idx);
		$stmt->execute();
		$Num = $stmt->rowCount();
		if($Num < 1)  { //아닐경우
			$chkResult = "0";
		} else {
			$chkResult = "1";
			while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
				$idx = $row['idx'];											// 지도고유번호
				if($con_reg_Id != $mem_Id){
					// 지점수 확인
					$cnt_query = "
						SELECT count(idx) as cnt
						FROM TB_PLACE
						WHERE con_Idx = :con_Idx
							AND open_Bit = '0'
							AND delete_Bit = '0'
							OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit = 'Y')
						ORDER BY reg_Date DESC;
					";
				}else{
					// 지점수 확인
					$cnt_query = "
						SELECT count(idx) as cnt
						FROM TB_PLACE
						WHERE con_Idx = :con_Idx
							AND delete_Bit = '0'
							OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit = 'Y')
						ORDER BY reg_Date DESC;
					";
				}
				$cnt_stmt = $DB_con->prepare($cnt_query);
				$cnt_stmt->bindParam(":con_Idx", $idx);
				$cnt_stmt->execute();
				$cnt_row=$cnt_stmt->fetch(PDO::FETCH_ASSOC);
				$pin_Cnt = $cnt_row['cnt'];								// 지도내 지점 갯수
				if($pin_Cnt == ''){
					$pin_Cnt = "0";
				}
				if($mem_Id != 'GUEST'){
					$subs_query = "
						SELECT *
						FROM TB_MEMBERS_SUBSCRIBE
						WHERE mem_Id = :mem_Id
							AND con_Idx = :con_Idx
							AND member_Idx = :member_Idx
							AND use_Bit = 'Y'
						ORDER BY reg_Date DESC;
						";
					$subs_stmt = $DB_con->prepare($subs_query);
					$subs_stmt->bindParam(":mem_Id", $mem_Id);
					$subs_stmt->bindParam(":member_Idx", $mIdx);
					$subs_stmt->bindParam(":con_Idx", $idx);
					$subs_stmt->execute();
					$subs_num = $subs_stmt->rowCount();
					if($subs_num < 1){
						$subs_bit = 'N';
					}else{
						$subs_bit = 'Y';
					}
				}else{
					$subs_bit = 'N';
				}
				$con_Name = $row['con_Name'];						// 지도명
				$con_Lv = $row['con_Lv'];								// 지도등급 (0: 본사, 1: 일반회원, 2: 제휴회원)
				$category = $row['category'];							// 카테고리
				// 카테고리명
				$category_Cquery = "
					SELECT code_Name
					FROM TB_CONFIG_CODE
					WHERE code = :code
						AND code_Div = 'category'
					ORDER BY reg_Date DESC;
					";
				$category_Cstmt = $DB_con->prepare($category_Cquery);
				$category_Cstmt->bindParam(":code", $category);
				$category_Cstmt->execute();
				while($category_Crow=$category_Cstmt->fetch(PDO::FETCH_ASSOC)) {
					$code_Name = $category_Crow['code_Name'];
					$category_query = "
						SELECT code_on_Img as code_Img
						FROM TB_CONFIG_CODE
						WHERE code_Name = :code_Name
							AND code_Div = 'categorylist'
						ORDER BY reg_Date DESC;
						";
					$category_stmt = $DB_con->prepare($category_query);
					$category_stmt->bindParam(":code_Name", $code_Name);
					$category_stmt->execute();
					while($category_row=$category_stmt->fetch(PDO::FETCH_ASSOC)){
						$code_Img = $category_row['code_Img'];
						if($code_Img == ''){
							$code_Img = '';
						}else{
							$code_ImgFile = "http://places.gachita.co.kr/udev/admin/data/code_img/photo.php?id=".$code_Img;
							$code_Img = $code_ImgFile;
						}
					}
				}
				$thumbnail_Bit = $row['thumbnail_Bit'];							//썸네일 사용여부
				if($thumbnail_Bit == "1"){
					//special 회원 인 경우는 썸네일 이미지 출력
					if($con_reg_Id != $mem_Id){
						// 이미지확인
						$img_query = "
							SELECT img as pimg
							FROM TB_PLACE
							WHERE con_Idx = :con_Idx
								AND open_Bit = '0'
								AND delete_Bit = '0'
								OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit ='Y')
							ORDER BY reg_Date DESC;
						";
					}else{
						// 이미지확인
						$img_query = "
							SELECT img as pimg
							FROM TB_PLACE
							WHERE con_Idx = :con_Idx
								AND delete_Bit = '0'
								OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit ='Y')
							ORDER BY reg_Date DESC;
						";
					}
					$img_stmt = $DB_con->prepare($img_query);
					$img_stmt->bindParam(":con_Idx", $idx);
					$img_stmt->execute();
					$img_Cnt = $img_stmt->rowCount();
					$p_Img = [];
					$img_Name = $row['img'];								// 이미지
					if($img_Name == ""){											// 이미지가 없을 경우 
						$locat_Img = "";
						array_push($p_Img, $locat_Img);
						$img = $p_Img;
					}else{
						$locat_Img = conImgInfo($idx);
						array_push($p_Img, $locat_Img);
						$img = $p_Img;
					}
				}else{
					if($con_reg_Id != $mem_Id){
						// 이미지확인
						$img_query = "
							SELECT img as pimg
							FROM TB_PLACE
							WHERE con_Idx = :con_Idx
								AND open_Bit = '0'
								AND delete_Bit = '0'
								OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit ='Y')
							ORDER BY reg_Date DESC;
						";
					}else{
						// 이미지확인
						$img_query = "
							SELECT img as pimg
							FROM TB_PLACE
							WHERE con_Idx = :con_Idx
								AND delete_Bit = '0'
								OR idx in (SELECT place_Idx FROM TB_MEMBERS_SHARE WHERE con_Idx = :con_Idx AND use_Bit ='Y')
							ORDER BY reg_Date DESC;
						";
					}
					$img_stmt = $DB_con->prepare($img_query);
					$img_stmt->bindParam(":con_Idx", $idx);
					$img_stmt->execute();
					$p_Img = [];
					$img_File = [];
					$img_Cnt = 0;
					while($img_row=$img_stmt->fetch(PDO::FETCH_ASSOC)){
						$pimg = $img_row['pimg'];	
						if($pimg == ''){
							continue;
						}
						$m_file = $_SERVER["DOCUMENT_ROOT"].'/contents/place_img/'.$pimg;
						if(is_dir($m_file)){
							// 핸들 획득
							$handle  = opendir($m_file);
							$filename = readdir($handle);
							$files = array();
							// 디렉터리에 포함된 파일을 저장한다.
							while (false !== ($filename = readdir($handle))) {
								if($filename == "." || $filename == ".."){
									continue;
								}
								// 파일인 경우만 목록에 추가한다.
								$f_dir = $m_file . "/" . $filename;
								if(is_file($f_dir)){
									$files[] = $filename;
								}
							}
							// 핸들 해제 
							closedir($handle);
							// 정렬, 역순으로 정렬하려면 rsort 사용
							rsort($files);
							// 파일명을 출력한다.
							$img_idx = 0;
							$imgCnt = 0;
							foreach ($files as $f) {
								$img_FileName = "http://places.gachita.co.kr/contents/place_img/".$pimg."/".$f;
								array_push($img_File, $img_FileName);
								$img_idx++;
							}
							$imgCnt = count($files);
							$img_Cnt = $img_Cnt + $imgCnt;
							$img = $img_File;
						}else{
							$img_Cnt = "0";
							$img = array('');
						}
					}
				}
				if($img_Cnt != "0"){
					$imgFile_Cnt = count($img);						
				}
				if($imgFile_Cnt > 5){
					foreach ($img as $key => $val) {
						if($key > 3){
							//삭제실행
							unset($img[$key]);
						}
					}
					array_values($img);
				}
				$tag = $row['tag'];										// 지점 태그
				if($tag == ''){
					$tag = '';
				}else{
					$tag = "#".str_replace(",",",#",$tag);
				}
				$like_Cnt = $row['like_Cnt'];							// 좋아요 수
				$kml_File = $row['kml_File'];							// kml 영역파일 유무
				if($kml_File == ''){
					$kml_File = '';
				}
				$memo = $row['memo'];								// 한줄메모
				if($memo == ''){
					$memo = '';
				}
				$reg_Id = $row['reg_Id'];									// 등록자
				$reg_Nname = memNickInfo($reg_Id);				// 회원닉네임
				$member_Img = memImgInfo($reg_Id);				// 회원이미지
				if($member_Img == ''){
					$member_Img = "";
				}
				if($reg_Nname == ''){
					$reg_Nname = "";
				}
				$reg_Date = $row['reg_Date'];							// 등록일
				$regDate = substr($reg_Date, 0, 10);
				$end_Date = $row['end_Date'];							// 등록기간종료일
				if($con_Lv == "1"){
					$endDate = ""; 
				}else{
					$endDate = substr($end_Date, 0, 10); 
				}
			}
			$result = array("result" => "success", "con_Idx" => $idx, "con_Name" => $con_Name, "con_Lv" => $con_Lv, "category" => $category, "code_Name" => $code_Name, "code_Img" => $code_Img,  "img_Cnt" => (string)$img_Cnt, "img" => $img, "tag" => $tag, "kml_File" => $kml_File, "memo" => $memo, "pin_Cnt" => (string)$pin_Cnt, "subs_bit" => $subs_bit, "member_Img" => $member_Img, "reg_Id" => $reg_Id, "nick_Name" =>$reg_Nname, "reg_Date" => $regDate, "end_Date" => $endDate);
		}  
		if ($chkResult  == "1") {
			echo json_encode($result, JSON_UNESCAPED_UNICODE); 
		} else if ($chkResult  == "0"  ) {
			$result = array("result" => "success", "errorMsg" => "자료가 없습니다.");
			echo json_encode($result, JSON_UNESCAPED_UNICODE); 
		}
	}else{
		$result = array("result" => "success", "errorMsg" => "삭제된 지도입니다.");
		echo json_encode($result, JSON_UNESCAPED_UNICODE); 
	}
    dbClose($DB_con);
    $stmt = null;
} else { //빈값일 경우
	$result = array("result" => "error", "errorMsg" => "지도고유번호오류");
	echo json_encode($result, JSON_UNESCAPED_UNICODE); 
}
?>


