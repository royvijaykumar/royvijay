<?php 
$title="Sourced Candidates";
$code="candidates";
$pagecode="teamleadercandidates";
include('admin_head.php');
include('admin_navbar.php');
@$key_skills = $_GET["key_skills"]; 
@$experience = $_GET["experience"]; 
@$salary = $_GET["salary"]; 
@$company = $_GET["company"]; 
@$designation = $_GET["designation"]; 
@$fun_area = $_GET["fun_area"]; 
@$industry = $_GET["industry"]; 
$key_arr = explode(",",$key_skills); 
$key_val='';
$key_val_sec='';

$querysel = mysqli_query($connection,"select * from universe_requirements");

$querysel_cnt = mysqli_num_rows($querysel);
$req_details = array();
if($querysel_cnt > 0)
{
	while($row=mysqli_fetch_assoc($querysel)) 
	{
		$req_details[] = $row;
	}
}

$querysel = mysqli_query($connection,"select * from universe_client order by client_name ASC");

$querysel_cnt = mysqli_num_rows($querysel);
$cli_details = array();
if($querysel_cnt > 0)
{
	while($row=mysqli_fetch_assoc($querysel)) 
	{
		$cli_details[] = $row;
	}
}


$showRecordPerPage = 20;
if(isset($_GET['page']) && !empty($_GET['page']))
{
	$currentPage = $_GET['page'];
}
else
{
	$currentPage = 1;
}
if(isset($_GET['last']) && !empty($_GET['last']))
{
	$lastpage = 1;
}
else
{
	$lastpage = 0;
}
$startFrom = ($currentPage * $showRecordPerPage) - $showRecordPerPage;


$conditions = " first_name != '' and sourcedby = ?";
$queryParams = array($user_id);
$queryTypes = "i";

if (!empty($_GET['candidate_name'])) 
{
	$likeCandidateName = '%' . $_GET['candidate_name'] . '%';
	$conditions .= " AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?)";
	$queryParams[] = $likeCandidateName;
	$queryParams[] = $likeCandidateName;
	$queryParams[] = $likeCandidateName;
	$queryTypes .= "sss";
}
if (!empty($_GET['candidate_email'])) 
{
	$conditions .= " AND email = ?";
	$queryParams[] = $_GET['candidate_email'];
	$queryTypes .= "s";
}
if (!empty($_GET['candidate_phone'])) 
{
	$conditions .= " AND contact_number1 = ?";
	$queryParams[] = $_GET['candidate_phone'];
	$queryTypes .= "s";
}

$empSQL = "SELECT * FROM `universe_candidates` WHERE" . $conditions . " order by candidate_id DESC LIMIT ?, ?";
$queryParamsWithPaging = $queryParams;
$queryParamsWithPaging[] = $startFrom;
$queryParamsWithPaging[] = $showRecordPerPage;
$queryTypesWithPaging = $queryTypes . "ii";

$stmt = mysqli_prepare($connection, $empSQL);
mysqli_stmt_bind_param($stmt, $queryTypesWithPaging, ...$queryParamsWithPaging);
mysqli_stmt_execute($stmt);
$canResult = mysqli_stmt_get_result($stmt);
$curresults = mysqli_num_rows($canResult);

$empSQL1 = "SELECT count(*) as total FROM `universe_candidates` WHERE" . $conditions . " order by candidate_id DESC";
$stmtCount = mysqli_prepare($connection, $empSQL1);
mysqli_stmt_bind_param($stmtCount, $queryTypes, ...$queryParams);
mysqli_stmt_execute($stmtCount);
$canResult1 = mysqli_stmt_get_result($stmtCount);
$candidatetotat = mysqli_fetch_assoc($canResult1);
//echo $empSQL1;
$totalresultts = $candidatetotat['total'];

$lastPage = ceil($totalresultts/$showRecordPerPage);
$firstPage = 1;
$nextPage = $currentPage + 1;
$previousPage = $currentPage - 1;
$candidate_name_value = htmlspecialchars($_GET['candidate_name'] ?? '', ENT_QUOTES, 'UTF-8');
$candidate_email_value = htmlspecialchars($_GET['candidate_email'] ?? '', ENT_QUOTES, 'UTF-8');
$candidate_phone_value = htmlspecialchars($_GET['candidate_phone'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!--Start Of Generate Report Tracker Section 1-->
<section>
	<div id="" class="container-fluid px-0">
		<div class="col-lg-12 col-md-12 col-sm-12 col-12" id="search_cv_results_section_total_content">
			<div class="row mx-0">
				<div class="col-lg-12 col-md-12 col-sm-12 col-12 px-0" id="client_list_details_section_1_search_dropdown_section">
					<div class="row mx-0">
						<div class="col-lg-12 col-md-12 col-sm-12 col-12 px-0">
							<form action="mycandidates.php" id="candidatesearchform" name="candidatesearchform">
								<div class="row mx-0">
									<div class="col-lg-3 col-md-3 col-12">
										<div class="form-group">
										  <label for="firstname">Candidate Name</label>
										  <input type="text" class="form-control" placeholder="" id="candidate_name" name="candidate_name" value="<?php echo $candidate_name_value; ?>">
										</div>
									</div>
									<div class="col-lg-3 col-md-3 col-12">
										<div class="form-group">
										  <label for="firstname">Candidate Email</label>
										  <input type="email" class="form-control" placeholder="" id="candidate_email" name="candidate_email" value="<?php echo $candidate_email_value; ?>">
										</div>
									</div>
									<div class="col-lg-3 col-md-3 col-12">
										<div class="form-group">
										  <label for="firstname">Candidate Phone</label>
										  <input type="text" onkeypress="return restrictAlphabets(event)" class="form-control" placeholder="" id="candidate_phone" name="candidate_phone" value="<?php echo $candidate_phone_value; ?>">
										</div>
									</div>												
									<div class="col-lg-3 col-md-3 col-12" style="margin-top:2.5%;">
										<div class="row mx-0">
											<div class="col-lg-6 col-md-6 col-sm-12 col-12">	
												<input type="submit" class="btn previous-resume" name="submit" value="Search" />
											</div>
											<div class="col-lg-6 col-md-6 col-sm-12 col-12">	
												<button type="button" id="button-clear" class="btn btn-primary pull-right"> Clear</button>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>	 
					</div>
					<div class="row mx-0">
						<div class="col-lg-6 col-md-6 col-sm-12 col-12 px-0" id="client_list_details_section_1_search_content_section">							
							 
						</div>	 
						<div class="col-lg-6 col-md-6 col-sm-12 col-12 text-right m-auto">
							<div class="client_list_details_section_1_client_dropdown_list">
								<a href="upload_cv.php"> Upload CV</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-12 col-md-12 col-sm-12 col-12 px-0" id="search_cv_result_1_right">
					<div class="form-group">
						<div class="row mx-0">	
							<div class="col-lg-12 col-md-12 col-12 px-0">	
								<form method="post" class="add_contact" id="add_requirement" name="add_requirement" action="add_requirement_process.php">
									<div class="row mx-0" id="search_cv_results_1_right_select_card_section_content">
										<div class="col-lg-12 col-md-12 col-sm-12 col-12 px-0">
											<div class="row mx-0" id="search_result_1_right_top_btn_and_dropdown_section">												
												<div class="col-lg-6 col-md-6 col-sm-12 col-12 text-left px-0" id="search_result_share_send_email_btn_section">
													<input type="checkbox" class="select-item checkbox" name="select-item" value="Select All" id="checkAll">
													<!--<button type="button" class="shareSelcted btn" id="shareSelcted">Share Selected</button>-->
													<input type="submit" class="sendEmail btn" id="sendEmail" value="Send email" name="email" />
													<!--<button type="button" class="sendSMS btn" id="sendSMS">Send SMS</button>-->
													<button type="button" class="sendSMS btn" id="addReq" style="width:130px;">Add to Requirments</button>
													<div class="col-lg-3 col-md-3 col-sm-12 col-12 px-0 m-auto" id="addReqdetails">
														<div class="row mx-0">
															<div class="row">
																<div class="form-group">
																<input type="hidden" value="" name="hidden_add_req" id="hidden_add_req" />
																  <select id="client_id" name="client_id" class="" required="required" onChange="getRequirement(this.value)">
																		<option value ="0">Choose Client Name</option>																	
																	<?php foreach($cli_details as $cli_detail) { ?>
																	<option value="<?php echo $cli_detail["client_id"]; ?>"><?php echo $cli_detail["client_name"]; ?></option>
																	<?php } ?>
																</select>
																</div>
															</div>
															<div class="row">
																<div class="form-group">
																  <select id="requirement" name="requirement_id" class="" required="required">
																		<option value ="0">Choose Requirement Name</option>																	
																	<?php foreach($req_details as $req_detail) { ?>
																	<option value="<?php echo $req_detail["requirement_id"]; ?>"><?php echo $req_detail["requirement_name"]; ?></option>
																	<?php } ?>
																</select>
																</div>
															</div>
															<div class="row">
																<div class="col-lg-4 col-md-4 col-sm-12 col-12"></div>
																	<div class="form-group">			
																		<input type="submit" name="submit" value="Add" />	
																	</div>
																<div class="col-lg-4 col-md-4 col-sm-12 col-12"></div>
															</div>
														</div>
													</div>
												</div>										
										</div>
										
										<div class="col-lg-12 col-md-12 col-sm-12 col-12 px-0" id="search_cv_results_tracker_1_right_side_select_list_section_content">
											<?php while($candidate = mysqli_fetch_assoc($canResult)){ ?>
											<div class="row mx-0" id="search_cv_results_tracker_1_right_side_select_list_section_content_row">
												<div class="col-lg-4 col-md-4 col-sm-12 col-12" id="search_cv_results_tracker_1_right_side_select_list_section_content_position_1">
													<div class="row mx-0">
														<div class="col-lg-1 col-md-1 col-sm-1 col-1 px-0 m-auto" id="">
															<h6> 
																<input type="checkbox" class="select-item-1 checkbox" name="select-item[]" value="<?php echo $candidate['candidate_id']; ?>">
															</h6>
														</div>
														
														<div class="col-lg-3 col-md-3 col-sm-3 col-3 px-0 m-auto" id="">																		
															<?php if($candidate['upload_photo'] != '') { ?>
																<a href="candidate_details.php?candidate_id=<?php echo $candidate['candidate_id']; ?>">
																	<img src="../candidates/photos/<?php echo $candidate['upload_photo']; ?>" class="img-fluid">
																</a>
															<?php } ?>
														</div>
														<div class="col-lg-8 col-md-8 col-sm-8 col-8 px-0" id="">
															<a href="candidate_details.php?candidate_id=<?php echo $candidate['candidate_id']; ?>">
																<h4 class="search_cv_results_lists_name"><?php echo $candidate['first_name']; ?> <?php echo $candidate['middle_name']; ?> <?php echo $candidate['last_name']; ?></h4>
																<h6 class="search_cv_results_lists_first_sub_name">Moile:<span>91 <?php echo $candidate['contact_number1']; ?></span></h6>
																<h6 class="search_cv_results_lists_first_sub_name">Email:<span><?php echo $candidate['email']; ?></span></h6>
																<p class="search_cv_results_lists_second_sub_name">Last updated:<span> <?php echo $candidate['updated_on']; ?></span></p>
															</a>
														</div>
													</div>
												</div>
												<div class="col-lg-4 col-md-4 col-sm-12 col-12 pl-0" id="search_cv_results_tracker_1_right_side_select_list_section_content_position_2">												
													<a href="candidate_details.php?candidate_id=<?php echo $candidate['candidate_id']; ?>">
														<h6 class="search_cv_results_lists_first_sub_name">Current<span> <?php echo $candidate['designation']; ?></span></h6>
														<h6 class="search_cv_results_lists_first_sub_name">Location:<span> <?php echo $candidate['work_location']; ?></span></h6>
														<h6 class="search_cv_results_lists_first_sub_name">Experience:<span> <?php echo $candidate['total_experience']; ?>+</span></h6>
														<h6 class="search_cv_results_lists_first_sub_name">Key skills:<span> <?php echo $candidate['primary_skills']; ?>,<?php echo $candidate['secondary_skills']; ?></span></h6>														
													</a>
												</div>
												<div class="col-lg-4 col-md-4 col-sm-12 col-12 pl-0" id="search_cv_results_tracker_1_right_side_select_list_section_content_position_3">							
													<?php
														 $candidate_id = $candidate['candidate_id'];
														$i=1;
														$sql="select * from universe_requirements ur left join universe_requirements_candidates urc on (ur.requirement_id=urc.requirement_id) where urc.candidate_id ='$candidate_id' ORDER BY ur.requirement_id DESC";
														
														$sql_query_execute = mysqli_query($connection,$sql);
														$entity_rows = mysqli_num_rows($sql_query_execute);
														if($entity_rows > 0)
														{
															while($sql_query_results=mysqli_fetch_array($sql_query_execute)) 
															{
																//print_r($sql_query_results);
																$requirement_id = $sql_query_results['requirement_id'];
																$candidate_id = $sql_query_results['candidate_id'];
																$reqdetail = mysqli_query($connection,"select * from universe_requirements_candidates_status where requirement_id='$requirement_id' and candidate_id='$candidate_id' order by requirement_candidates_status_id desc limit 0,1");
																$reqclientdetail_result = mysqli_fetch_array($reqdetail,MYSQLI_ASSOC);
																$status = $reqclientdetail_result['status'];
																$stage = $reqclientdetail_result['stage'];
																$interview = $reqclientdetail_result['interview'];
															?>
															<p class="search_cv_results_lists_second_sub_name">
																<?php echo $sql_query_results['requirement_name']; ?> , 
																Status <?php echo $status; ?> 
																<a href="requirement_candidates.php?interview=<?php echo $interview; ?>&requirement_id=<?php echo $requirement_id; ?>&candidate_id=<?php echo $candidate_id; ?>&stage=<?php echo $stage; ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
															</p>					
													<?php } } ?>
												</div>
											</div>
											<?php } ?>
										</div>
									</div>								
								</form>
								<nav aria-label="Page navigation">
									<ul class="pagination">
										<?php if($currentPage != $firstPage) { ?>
											<li class="page-item">
												<a class="page-link" href="?page=<?php echo $firstPage ?>" tabindex="-1" aria-label="Previous">
													<span aria-hidden="true">First</span>
												</a>
											</li>
										<?php } ?>
										<?php if($currentPage >= 2) { ?>
											<li class="page-item">
												<a class="page-link" href="?page=<?php echo $previousPage ?>"><?php echo $previousPage ?></a>
											</li>
										<?php } ?>
										<li class="page-item active">
											<a class="page-link" href="?page=<?php echo $currentPage ?>"><?php echo $currentPage ?></a>
										</li>
										<?php if($currentPage != $lastPage) { ?>
											<li class="page-item">
												<a class="page-link" href="?page=<?php echo $nextPage ?>"><?php echo $nextPage ?></a>
											</li>
											<li class="page-item">
												<a class="page-link" href="?page=<?php echo $lastPage ?>&last=true" aria-label="Next">
													<span aria-hidden="true">Last</span>
												</a>
											</li>
										<?php } ?>
										
									</ul>
									
									<span>
										<?php if($lastpage == 0) { ?>
											<?php if(($totalresultts > $showRecordPerPage) && ($curresults == $showRecordPerPage)) { ?>
												<?php echo ($currentPage * $showRecordPerPage); ?>
											<?php } else { ?>
												<?php echo $totalresultts; ?>
											<?php } ?> of <?php echo $totalresultts; ?>
										<?php } else if($lastpage == 1) { ?>
											<?php echo ($totalresultts) ?> of <?php echo $totalresultts; ?>
										<?php } ?>
									</span>	
										
								</nav>
							</div>				
						</div>
					</div>			
				</div>
			</div>
		</div>
	</div>
</section>
<script>
$("#checkAll").change(function(){
	$('.select-item-1').prop('checked', this.checked);
});
$('#perpage').change(function() {
	window.location = $(this).val();
});
function getRequirement(val) 
{
	$.ajax({
		type: "POST",
		url: "getRequirements.php",
		data:'client_id='+val,
		success: function(data){
			$("#requirement").html(data);
		}
	});
}
$(function(){
	$('#sendEmail').click(function(){ 
		var values = [];
		$('.select-item-1:checked').each(function(i){ 
			values.push($(this).val());
			$('#hidden_add_req').val(values);
		});
	});
	$('#addReqdetails').hide();
	$('#addReq').click(function(){
		if($(this).is(".active")) 
		{
			$(this).removeClass("active");
			$('#addReqdetails').hide();
		}
		else
		{
			$(this).addClass("active");
			$('#addReqdetails').show();
		}
		var values = [];
		$('.select-item-1:checked').each(function(i){
			values.push($(this).val());
			
			$('#hidden_add_req').val(values);
		});
	});
});

$('#button-clear').on('click', function() {
	
	url = 'mycandidates.php';
	location = url;
});
</script>
<style>
#addReqdetails
{
	padding-top:5px;
}
</style>
</body>
</html>
