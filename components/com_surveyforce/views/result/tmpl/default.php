<?php
/**
 *   @package         Surveyforce
 *   @version           1.2-modified
 *   @copyright       JooPlce Team, 臺北市政府資訊局, Copyright (C) 2016. All rights reserved.
 *   @license            GPL-2.0+
 *   @author            JooPlace Team, 臺北市政府資訊局- http://doit.gov.taipei/
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$sid = $app->input->getInt("sid");
$itemid = $app->input->getInt("Itemid");

$quest_index = 0;  // 第幾題用
$field_count = array (); // 票數
$total_count = array (); // 總票數
$pieid = array (); // 圓餅圖id
$result_num = $this->item->result_num; // 顯示數目
$qtype = array ("select", "number", "table"); // 有子選項的題目類型

$king = "<img src='" . JURI::root() . "images/system/king.gif' alt='獲選' width='50'>"; // 皇冠 icon

$session = &JFactory::getSession();
$prac = $session->get('practice_pattern');
if ($prac) {
	$practice_vote = array ();
}

?>

<script src="components/com_surveyforce/assets/js/dist/echarts.js" ></script>
<script src="https://d3js.org/d3.v3.min.js"></script>

<div class="survey survey_result">

    <div class="title">
		<?php echo $this->item->title; ?>
    </div>

    <div class="ordertype">
        投票結果排序方式：
        <a href="<?php echo JRoute::_("index.php?option=com_surveyforce&view=result&sid={$sid}&orderby=0&chart={$this->chart}&Itemid={$itemid}"); ?>" title="依選項排序" class="<?php
		if ($this->orderby == 0) {
			echo "active";
		}
		?>">依選項</a>
        <a href="<?php echo JRoute::_("index.php?option=com_surveyforce&view=result&sid={$sid}&orderby=1&chart={$this->chart}&Itemid={$itemid}"); ?>" title="依票數排序" class="<?php
		if ($this->orderby == 1) {
			echo "active";
		}
		?>">依票數</a>
    </div>


    <div class="ordertype">
        結果圖形呈現方式：
        <a href="<?php echo JRoute::_("index.php?option=com_surveyforce&view=result&sid={$sid}&orderby={$this->orderby}&chart=bar&Itemid={$itemid}"); ?>" title="長條圖" class="<?php
		if ($this->chart == "bar") {
			echo "active";
		}
		?>">長條圖</a>
        <a href="<?php echo JRoute::_("index.php?option=com_surveyforce&view=result&sid={$sid}&orderby={$this->orderby}&chart=pie&Itemid={$itemid}"); ?>" title="圓餅圖" class="<?php
		if ($this->chart == "pie") {
			echo "active";
		}
		?>">圓餅圖</a>
    </div>

	<?php
	if ($prac) {  //練習區
		if ($this->results) {
			foreach ($this->results as $key => $result) {
				$quest_index++;
				?> 
				<div class="result_block">
					<div class="quest_title">
						<?php
						if ($result->quest_type != 'open') {
							echo (count($this->results) > 1) ? "第" . $quest_index . "題：" : "題目：";
							echo $title;
						}
						?>
					</div>
					<?php
					if ($result->quest_type != 'open' && time() > 0) {
						echo "<div style='text-align:right'>資料更新時間：" . date("Y-m-d H:i") . " </div>";
					}
					?>
					<div class="quest_result">
						<?php if (in_array($result->quest_type, $qtype)) { ?>  <?php //有子選項   ?>
							<?php foreach ($result->field_title as $pkey => $field_title) { ?>	
								<div class="ftitle"><?php echo $field_title; ?></div>
								<div class="result_inner">
									<?php //table   ?>
									<div class="qtable">
										<table class="votetable">
											<thead>
												<tr>
													<th>投票類別</th>
													<th>得票數</th>
												</tr>
											</thead>
											<tbody>
												<?php
												foreach ($this->sub_fields[$key] as $skey => $sub_fields) {
													$practice_vote[$key . "_" . $pkey]["Practice"][$sub_fields] = $this->sub_results[$pkey . "_" . $skey] ? 1 : 0;
												}
												if ($this->orderby == 1) {
													arsort($practice_vote[$key . "_" . $pkey]["Practice"]);
												}
												$num = 0;
												foreach ($practice_vote[$key . "_" . $pkey]["Practice"] as $sub_fields => $votes) {
													?>
													<tr>
														<td class="cat">
															<?php echo $votes == 1 ? $king : ""; ?>
															<?php echo $sub_fields; ?>
														</td>
														<td><?php echo $votes; ?></td>
													</tr>
													<?php
												}
												// 統計數目
												if ($votes) {
													$num++;
												}

												$piechar_height = ($num * 30 ) + 20 + (($this->orderby == 1) ? ($num * 10 ) : 0);
												$piechar_height = ($piechar_height < 300) ? 320 : $piechar_height;
												?>
											</tbody>
										</table>
									</div>
									<br>
									<?php
									if ($this->chart == "pie") {
										//圓餅圖 
										?>                                                                          
										<div class="qchart" id="piePractice<?php echo $key . "_" . $pkey; ?>" style="width: 100%; height:<?php echo $piechar_height; ?>px; float: left;"></div>
										<div class="InP"><strong>投票類別：得票數</strong></div>
										<?php $pieid[$key . "_" . $pkey]["Practice"] = $key . "_" . $pkey; ?>  
										<?php
									} else {
										?>
										<svg class="chart<?php echo $key . "_" . $pkey; ?> svg_chart"></svg>
									<?php } ?>
								</div>
								<?php
							}
						} else {
							if ($result->quest_type == 'open') {
								/* 補齊div結尾後再略過 */
								echo "</div></div>";
								continue;
							}
							//無子選項 
							?>
							<div class="result_inner">
								<?php //table     ?>
								<div class="qtable">
									<table class="votetable">
										<thead>
											<tr>
												<th>投票類別</th>
												<th>得票數</th>
											</tr>
										</thead>
										<tbody>
											<?php
											//沒有子選項的題目類型 text、img、textimg...
											foreach ($result->field_title as $fkey => $field_title) {  //寫入選項名稱及票數
												$practice_vote[$key]["Practice"][$field_title] = $result->count[$fkey] ? 1 : 0;
											}
											if ($this->orderby == 1) {
												arsort($practice_vote[$key]["Practice"]);
											}
											$num = 0;
											foreach ($practice_vote[$key]["Practice"] as $field_title => $votes) {
												?>
												<tr>
													<td class="cat">
														<?php echo $votes == 1 ? $king : ""; ?>
														<?php echo $field_title; ?>
													</td>
													<td><?php echo $votes; ?></td>
												</tr>
												<?php
											}
											// 統計數目
											if ($votes) {
												$num++;
											}

											$piechar_height = ($num * 30 ) + 20 + (($this->orderby == 1) ? ($num * 10 ) : 0);
											$piechar_height = ($piechar_height < 300) ? 320 : $piechar_height;
											?>
										</tbody>
									</table>
								</div>
								<br>
								<?php
								if ($this->chart == "pie") {
									//圓餅圖 
									?>                                                                          
									<div class="qchart" id="piePractice<?php echo $key; ?>" style="width: 100%; height:<?php echo $piechar_height; ?>px; float: left;"></div>
									<div class="InP"><strong>投票類別：得票數</strong></div>
									<?php $pieid[$key]["Practice"] = $key; ?>  
									<?php
								} else {
									?>
									<svg class="chart<?php echo $key; ?> svg_chart"></svg>
								<?php } ?>
							</div>							
						<?php } ?>
					</div>
				</div>
				<?php
			}
			unset($num);
		} else {
			echo "尚無資料顯示";
		}
	} else {  //正式區
		if ($this->results) {
			?>
			<?php
			foreach ($this->results as $key => $result) {
				$quest_index++;
				?>
				<div class="result_block">
					<div class="quest_title">
						<?php
						if ($result->quest_type != 'open') {
							echo (count($this->results) > 1) ? "第" . $quest_index . "題：" : "題目：";
							echo $result->quest_title;
						}
						?>
					</div>
					<?php
					if ($result->quest_type != 'open' && strtotime($this->update_time) > 0) {
						echo "<div style='text-align:right'>資料更新時間：" . JHtml::_('date', $this->update_time, "Y-m-d H:i") . " (每5分鐘更新)</div>";
					}
					?>
					<div class="quest_result">
						<?php //沒有子選項的題目類型 text、img、textimg  ?>
						<?php if (!in_array($result->quest_type, $qtype)) { ?>
							<?php
							if ($result->quest_type == 'open') {
								/* 補齊div結尾後再略過 */
								echo "</div></div>";
								continue;
							}
							// $key: question_id
							// $fkey、$ckey: field_id
							?>
							<div class="result_inner">
								<?php //table    ?>
								<div class="qtable">
									<table class="votetable">
										<thead>
											<tr>
												<th>投票類別</th>
												<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
													<th>總得票數</th>
												<?php } else { ?>
													<th>得票數</th>
												<?php } ?>
												<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
													<th>網路</th>
												<?php } ?> 
												<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
													<th>現地</th>
												<?php } ?>                                                                                   
											</tr>
										</thead>

										<tbody>
											<?php
											unset($field_count);
											unset($total_count);
											foreach ($this->fields[$key] as $fkey => $field) {
												$field_count[$fkey] = ($result->count[$fkey]) ? (int) ($result->count[$fkey]) : 0;
												$total_count[$fkey] = $field_count[$fkey];

												// 是否有紙本或現地投票
												if ($this->paper[$key] || $this->place[$key]) {
													$total_count[$fkey] += (int) $this->paper[$key][$fkey];
													$total_count[$fkey] += (int) $this->place[$key][$fkey];
													$chart[$key]["Total"][$field] = (int) $total_count[$fkey];
													$chart[$key]["Internet"][$field] = (int) $field_count[$fkey];
													$chart[$key]["Present"][$field] += (int) $this->paper[$key][$fkey];
													$chart[$key]["Present"][$field] += (int) $this->place[$key][$fkey];
												} else {
													$chart[$key]["Internet"][$field] = (int) $total_count[$fkey];
												}
											}
											if ($this->orderby == 1) {  // 票數排序
												foreach ($chart[$key] as $vote_type => $vote_num) {
													arsort($vote_num);
													$chart[$key][$vote_type] = $vote_num;
												}
												arsort($vote_num);
												arsort($total_count);
												$rank_count = array_count_values($total_count);
												$r = 0;
												foreach ($rank_count as $rkey => $count) {
													$r++;
													if ($this->item->result_num_type == 0 && $r == 1) {
														$king_num = $rkey;
														break;
													} elseif ($r == $result_num) {
														$king_num = $rkey;
														break;
													}
												}
											} else if ($this->orderby == 0) {  // 選項排序
												$order_arr = $total_count;
												arsort($order_arr);
												$rank_count = array_count_values($order_arr);
												unset($rank_count[0]);
												$r = 0;
												foreach ($rank_count as $rkey => $count) {
													$r++;
													if ($r == $result_num or $r == count($rank_count)) {
														$king_num = $rkey;
														break;
													}
												}

												$king_arr = array ();
												$temp_order = 0;
												foreach ($order_arr as $okey => $order) {
													if ($this->item->result_num_type == 0) {
														if ($rank_count[order] == 1) {
															$king_arr[$okey] = $okey;
															break;
														} else {
															if ($temp_order != $order && $temp_order != 0) {
																break;
															}
															$king_arr[$okey] = $okey;
															$temp_order = $order;
														}
													} else if ($order != 0) {
														if ($order >= $king_num) {
															$king_arr[$okey] = $okey;
														} else {
															break;
														}
													}
												}
											}
											$num = 0;

											foreach ($total_count as $ckey => $count) {
												// for pie charts                                            
												$field_name = $this->fields[$key][$ckey];
												?>

												<tr>
													<td class="cat">
														<?php
														if ($this->orderby == 1) {
															echo (($count >= $king_num) && $count != 0) ? $king : "";
														} elseif ($this->orderby == 0) {
															if (in_array($ckey, $king_arr)) {
																echo $king;
															}
														}
														?>
														<?php echo $field_name; ?>
													</td>
													<td><?php echo $count; ?></td>
													<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
														<td><?php echo $field_count[$ckey]; ?></td>
													<?php } ?>
													<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
														<td><?php echo sprintf("%0d", $this->place[$key][$ckey]) + sprintf("%0d", $this->paper[$key][$ckey]); ?></td>
													<?php } ?>

												</tr>

												<?php
												// 統計數目
												if ($count) {
													$num++;
												}
											}
											$piechar_height = ($num * 30 ) + 20 + (($this->orderby == 1) ? ($num * 10 ) : 0);
											$piechar_height = ($piechar_height < 300) ? 320 : $piechar_height;
											?>
										</tbody>
									</table>
								</div>

								<br>
								<?php if ($this->chart == "pie") { ?>
									<?php // 圓餅圖 ?>
									<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
										<?php //total   ?>
										<div class="qchart" id="pieTotal<?php echo $key ?>" style="height:<?php echo $piechar_height; ?>px;"></div>
										<div class="InP"><strong>投票類別：總得票數</strong></div>
										<?php $pieid[$key]["Total"] = $key; ?>	
										<br><br>
									<?php } ?>				
									<?php //網路    ?>
									<div class="qchart" id="pieInternet<?php echo $key; ?>" style="height:<?php echo $piechar_height; ?>px;"></div>
									<div class="InP">
										<strong>投票類別：
											<?php if ($this->item->is_place == 1 || $this->paper[$key]) { ?>
												網路投票
											<?php } else { ?>
												得票數
											<?php } ?>
										</strong>
									</div>
									<?php $pieid[$key]["Internet"] = $key; ?>
									<?php //現地+紙本、total ?>
									<?php
									if ($this->item->is_place == 1 || $this->paper[$key]) {
										?>
										<br><br>
										<?php //現地+紙本     ?>
										<div class="qchart" id="piePresent<?php echo $key ?>" style="height:<?php echo $piechar_height; ?>px;"></div>
										<div class="InP"><strong>投票類別：現地投票</strong></div>
										<?php $pieid[$key]["Present"] = $key; ?>

										<?php
									}
								} else {
									?>
									<svg class="chart<?php echo $key; ?> svg_chart"></svg>
								<?php } ?>

							</div>

							<?php //題目類型 select、number、table    ?>
						<?php } else { ?>
							<?php
							// $key: question_id
							// $fkey: field_id
							// $sfkey、$ckey: sub_field_id
							?>
							<?php foreach ($result->field_title as $fkey => $field_title) { ?>
								<div class="ftitle"><?php echo $field_title; ?></div>
								<div class="result_inner">


									<?php // table     ?>
									<div class="qtable">
										<table class="votetable">
											<thead>
												<tr>
													<th><?php echo ($result->quest_type == "number") ? "投票分數" : "投票類別"; ?></th>
													<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
														<th>總得票數</th>
													<?php } else { ?>
														<th>得票數</th>
													<?php } ?>
													<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
														<th>網路</th>
													<?php } ?>    
													<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
														<th>現地</th>
													<?php } ?>                                                                                          
												</tr>
											</thead>

											<tbody>
												<?php
												unset($field_count);
												unset($total_count);

												foreach ($this->sub_fields[$key] as $sfkey => $sub_field) {
													$index = $fkey . "_" . $sfkey;
													$field_count[$sfkey] = ($this->sub_results[$index]->count) ? (int) ($this->sub_results[$index]->count) : 0;
													$total_count[$sfkey] = $field_count[$sfkey];

													// 是否有紙本或現地投票
													if ($this->sub_paper[$fkey] || $this->sub_place[$fkey]) {
														$total_count[$sfkey] += (int) $this->sub_paper[$fkey][$sfkey];
														$total_count[$sfkey] += (int) $this->sub_place[$fkey][$sfkey];
														$chart[$key . '_' . $fkey]["Total"][$sub_field] = (int) $total_count[$sfkey];
														$chart[$key . '_' . $fkey]["Internet"][$sub_field] = (int) $field_count[$sfkey];
														$chart[$key . '_' . $fkey]["Present"][$sub_field] += (int) $this->sub_paper[$fkey][$sfkey];
														$chart[$key . '_' . $fkey]["Present"][$sub_field] += (int) $this->sub_place[$fkey][$sfkey];
													} else {
														$chart[$key . '_' . $fkey]["Internet"][$sub_field] = (int) $total_count[$sfkey];
													}
												}

												if ($this->orderby == 1) {  // 票數排序
													foreach($chart[$key . '_' . $fkey] as $vote_type => $vote_num){
														arsort($vote_num);
														$chart[$key . '_' . $fkey][$vote_type] = $vote_num;
													}
													arsort($total_count);
													$rank_count = array_count_values($total_count);
													$r = 0;
													foreach ($rank_count as $rkey => $count) {
														$r++;
														if ($this->item->result_num_type == 0 && $r == 1) {
															$king_num = $rkey;
															break;
														} elseif ($r == $result_num) {
															$king_num = $rkey;
															break;
														}
													}
												} else if ($this->orderby == 0) {  // 選項排序
													$order_arr = $total_count;
													arsort($order_arr);
													$rank_count = array_count_values($order_arr);
													unset($rank_count[0]);
													$r = 0;
													foreach ($rank_count as $rkey => $count) {
														$r++;
														if ($r == $result_num or $r == count($rank_count)) {
															$king_num = $rkey;
															break;
														}
													}


													$king_arr = array ();
													$temp_order = 0;
													foreach ($order_arr as $okey => $order) {
														if ($this->item->result_num_type == 0) {
															if ($rank_count[order] == 1) {
																$king_arr[$okey] = $okey;
																break;
															} else {
																if ($temp_order != $order && $temp_order != 0) {
																	break;
																}
																$king_arr[$okey] = $okey;
																$temp_order = $order;
															}
														} else if ($order != 0) {
															if ($order >= $king_num) {
																$king_arr[$okey] = $okey;
															} else {
																break;
															}
														}
													}
												}


												$num = 0;

												foreach ($total_count as $ckey => $count) {
													// for pie charts
													$field_name = $this->sub_fields[$key][$ckey];
													?>
													<tr>
														<td class="cat">
															<?php
															if ($this->orderby == 1) {
																echo (($count >= $king_num) && $count != 0) ? $king : "";
															} elseif ($this->orderby == 0) {
																if (in_array($ckey, $king_arr)) {
																	echo $king;
																}
															}
															?>
															<?php echo $field_name; ?>
														</td>
														<td><?php echo $count ?></td>
														<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
															<td><?php echo $field_count[$ckey]; ?></td>
														<?php } ?>
														<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
															<td><?php echo sprintf("%0d", $this->sub_place[$fkey][$ckey]) + sprintf("%0d", $this->sub_paper[$fkey][$ckey]); ?></td>
														<?php } ?>
													</tr>

													<?php
													// 統計數目
													if ($count) {
														$num++;
													}
												}

												$piechar_height = ($num * 30 ) + 20 + (($this->orderby == 1) ? ($num * 10 ) : 0);
												$piechar_height = ($piechar_height < 300) ? 320 : $piechar_height;
												?>
											</tbody>
										</table>
									</div>
									<br>
									<?php
									if ($this->chart == "pie") {
										//圓餅圖 
										//Total
										if ($this->item->is_place == 1 || $this->sub_paper) {
											?>                                                                          
											<div class="qchart" id="pieTotal<?php echo $key . "_" . $fkey; ?>" style="width: 100%; height:<?php echo $piechar_height; ?>px; float: left;"></div>
											<div class="InP"><strong>投票類別：總得票數</strong></div>
											<?php $pieid[$key . "_" . $fkey]["Total"] = $key . "_" . $fkey; ?>  
											<br><br>
											<?php
										}
										//網路 
										?>
										<div class="qchart" id="pieInternet<?php echo $key . "_" . $fkey; ?>" style="width: 100%; height:<?php echo $piechar_height; ?>px; float: left;"></div>
										<div class="InP">
											<strong>
												投票類別：
												<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
													網路投票
												<?php } else { ?>
													得票數
												<?php } ?>
											</strong>
										</div>
										<?php $pieid[$key . "_" . $fkey]["Internet"] = $key . "_" . $fkey; ?>
										<?php //現地、Total ?>
										<?php if ($this->item->is_place == 1 || $this->sub_paper) { ?>
											<?php //現地 + 紙本   ?>
											<br><br>
											<div class="qchart" id="piePresent<?php echo $key . "_" . $fkey; ?>" style="width: 100%; height:<?php echo $piechar_height; ?>px; float: left;"></div>
											<div class="InP"><strong>投票類別：現地投票</strong></div>
											<?php $pieid[$key . "_" . $fkey]["Present"] = $key . "_" . $fkey; ?>
											<?php
										}
									} else {
										?>
										<svg class="chart<?php echo $key . '_' . $fkey; ?> svg_chart"></svg>
									<?php } ?>

								</div>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			<?php } ?>

			<?php if ($this->item->result_desc) { ?>
				<div class="result_desc">
					<?php
					echo $this->item->result_desc;
					?>
				</div>
			<?php } ?>

			<?php
		} else {
			echo "尚無資料顯示";
		}
	}


	// 處理$pie/$bar資料
	unset($chart_temp);
	if ($prac) {
		$chart_temp = $practice_vote;
		unset($practice_vote);
	} else {
		$chart_temp = $chart;
		unset($chart);
	}



	if ($chart_temp) {
		if ($this->chart == "pie") {
			$pie = array ();
			foreach ($chart_temp as $key => $item) {
				unset($zero_field_name);
				$zero_field_name = array ();

				foreach ($item as $occ => $value) {

					if ($value) {
						$pie[$key][$occ] = $value;
					} else {
						if (count($zero_field_name) == 3) {
							$zero_field_name[] = "及其他項目";
						} else if (count($zero_field_name) > 3) {
							continue;
						} else {
							$zero_field_name[] = $field_name;
						}
					}
				}

				if ($zero_field_name) {
					$field_name = implode("、", $zero_field_name);
					$pie[$key][$occ][$field_name] = 0;
				}
			}
		} else {
			$bar = $chart_temp;
		}
	}
	?>
</div>
<div class="mod_return return">
	<div class="return_inner home">
		<a href="<?php echo JURI::root(); ?>"><span><?php echo JText::_('JGLOBAL_BACK_HOME'); ?></span></a>
	</div>
	<noscript>您的瀏覽器不支援script程式碼。請使用"Backspace"按鍵回到上一頁。</noscript>
</div>

<script type="text/javascript" >
<?php
if ($this->chart == "pie") {
// 圓餅圖
	?>
		require.config ({
		paths: {
		echarts : '<?php echo JURI::base(); ?>components/com_surveyforce/assets/js/dist'
		}
		});
		require (
		[
				'echarts',
				'echarts/chart/pie'
		],
				drawEcharts
				);
		function drawEcharts(ec) {
	<?php
	foreach ($pieid as $key => $id) {
		foreach ($id as $occ => $value) {
			?>
				drawPie<?php echo $occ . $value; ?>(ec);
			<?php
		}
	}
	?>
		}
	<?php
	foreach ($pie as $key => $p) {
		foreach ($p as $occ => $value) {
			$filed_str = "'" . implode("','", array_keys($value)) . "'";
			$filed_num = count($value);
			$count = 0;
			?>
				function drawPie<?php echo $occ . $key; ?> (ec)  {

				var myChart = ec.init(document.getElementById('pie<?php echo $occ . $key; ?>'));
				var option = {
				tooltip : {
				trigger: 'item',
						formatter: "{a} <br/>{b} : {c} ({d}%)"
				},
						legend: {
						orient : 'horizontal',
								x : 'left',
								selectedMode : false,
								data:[<?php echo $filed_str; ?>],
								textStyle: {
								fontSize: 18
								}
						},
						calculable : false,
						series : [
						{
						name:'投票結果',
								type:'pie',
								radius : '<?php echo ($this->device == 3) ? "30%" : "50%"; ?>',
								center: ['50%', '65%'],
								data:[
			<?php
			foreach ($value as $pkey => $num) {
				$count++;
				?>
									{value:<?php echo $num; ?>, name:'<?php echo $pkey; ?>'}

				<?php
				if ($filed_num != $count) {
					echo ",";
				}
			}
			?>
								],
								itemStyle:{
								normal:{
								label:{
								show: true,
										formatter: '{b} : {c} \n ({d}%)',
										textStyle: {
										fontSize: 18
										}
								},
										labelLine :{show:true, length:<?php echo ($this->device == 3) ? "10" : "40"; ?>}
								}
								}
						}
						]
				};
				myChart.setOption (option, true);
				}

			<?php
		}
	}
} else {



// 長條圖
	$cat = array ("Internet" => "網路投票", "Present" => "現地投票", "Total" => "總得票數", "Practice" => "得票數");

	foreach ($bar as $key => $p) {
		?>
			var data<?php echo $key; ?> = {
			labels: [
		<?php
// labels為選項名稱
		$i = 0;
		$a = 0;
		foreach ($p as $occ => $value) {
			if ($a > 0) {
				continue;
			}
			$count = count($value);
			foreach ($value as $pkey => $num) {
				?>
					'<?php
				// device 為3 表手機板，其他為大中板

				switch ($this->device) {
					case 2:
						echo mb_strwidth($pkey) > 24 ? JHtml::_('utility.cutString', $pkey, 24) . '...' : $pkey;
						break;
					case 3:
						echo mb_strwidth($pkey) > 8 ? JHtml::_('utility.cutString', $pkey, 8) . '...' : $pkey;
						break;
					default:
					case 1:
						echo mb_strwidth($pkey) > 30 ? JHtml::_('utility.cutString', $pkey, 30) . '...' : $pkey;
						break;
				}
				?>'
				<?php
				$i++;
				if ($i != $count) {  //如為最後一圈就不加,
					echo ',';
				}
			}
			$a++;
		}
		?>
			],
					series: [
		<?php
		$count2 = count($p);
		$k = 0;
		foreach ($p as $occ => $value) {
			if (!$prac && !$p["Present"]) {
				$cat["Internet"] = "得票數";
			}
			$k++;
			$count1 = count($value);
			$j = 0;
			?>
						{
						label: '<?php echo $cat[$occ]; ?>', //投票類別
								values: [
			<?php
			foreach ($value as $pkey => $num) {
				$j++;
				echo $num;  //得票數
				if ($j != $count1) {
					echo ',';  //如為最後一圈就不加,
				}
			}
			?>
								]
						}
			<?php
			if ($count2 != $k) {
				echo ',';   //如為最後一圈就不加,
			}
		}
		?>

					]

			};
			var chartWidth = <?php 
				switch ($this->device) {

					case 2:
						echo "450";
						break;
					case 3:
						echo "170";
						break;
					default:
					case 1:
						echo "600";
						break;
				}
				?>,
					barHeight = 20,
					groupHeight = barHeight * data<?php echo $key; ?>.series.length,
					gapBetweenGroups = 10,
					spaceForLabels = <?php 
						switch ($this->device) {
							case 2:
								echo "250";
								break;
							case 3:
								echo "100";
								break;
							case 1:
							default:	
								echo "350";
								break;
						}
					?>,
					spaceForLegend = 150;
			// Zip the series data together (first values, second values, etc.)
			var zippedData = [];
			for (var i = 0; i < data<?php echo $key; ?>.labels.length; i++) {
			for (var j = 0; j < data<?php echo $key; ?>.series.length; j++) {
			zippedData.push(data<?php echo $key; ?>.series[j].values[i]);
			}
			}
			// Color scale
			var color = d3.scale.category20();
			var chartHeight = barHeight * zippedData.length + gapBetweenGroups * data<?php echo $key; ?>.labels.length;
			var x = d3.scale.linear()
					.domain([0, d3.max(zippedData)])
					.range([0, chartWidth]);
			var y = d3.scale.linear()
					.range([chartHeight + gapBetweenGroups, 0]);
			var yAxis = d3.svg.axis()
					.scale(y)
					.tickFormat('')
					.tickSize(0)
					.orient("left");
			// Specify the chart area and dimensions
			var chart = d3.select(".chart<?php echo $key; ?>")
					.attr("width", spaceForLabels + chartWidth)
					.attr("height", chartHeight + gapBetweenGroups * 2 * i + gapBetweenGroups * i);
			// Create bars
			var bar = chart.selectAll("g")
					.data(zippedData)
					.enter().append("g")
					.attr("transform", function(d, i) {
					return "translate(" + spaceForLabels + "," + (i * barHeight + gapBetweenGroups * (0.5 + Math.floor(i / data<?php echo $key; ?>.series.length))) + ")";
					});
			// Create rectangles of the correct width
			bar.append("rect")
					.attr("fill", function(d, i) { return color(i % data<?php echo $key; ?>.series.length); })
					.attr("class", "bar")
					.attr("width", x)
					.attr("height", barHeight - 1);
			// Add text label in bar
			bar.append("text")
					.attr("x", function(d) { if (d == 0){ return 12; } else{ return x(d) - 3; } })
					.attr("y", barHeight / 2)
					.attr("fill", "red")
					.attr("dy", ".35em")
					.text(function(d) { return d; });
			// Draw labels
			bar.append("text")
					.attr("class", "label")
					.attr("x", function(d) { return - 10; })
					.attr("y", groupHeight / 2)
					.attr("dy", ".35em")
					.text(function(d, i) {
					if (i % data<?php echo $key; ?>.series.length === 0)
							return data<?php echo $key; ?>.labels[Math.floor(i / data<?php echo $key; ?>.series.length)];
					else
							return ""});
			chart.append("g")
					.attr("class", "y axis")
					.attr("transform", "translate(" + spaceForLabels + ", " + - gapBetweenGroups / 2 + ")")
					.call(yAxis);
			// Draw legend (圖例)

			var legendRectSize = 18,
					legendSpacing = 4;
			var legend = chart.selectAll('.legend')
					.data(data<?php echo $key; ?>.series)
					.enter()
					.append('g')
					.attr('transform', function (d, i) {
					var height = legendRectSize + legendSpacing;
					var offset = - gapBetweenGroups / 2;
					var horz = spaceForLabels;
					var vert = chartHeight + i * height - offset + legendRectSize;
					return 'translate(' + horz + ',' + vert + ')';
					});
			legend.append('rect')
					.attr('width', legendRectSize)
					.attr('height', legendRectSize)
					.style('fill', function (d, i) { return color(i); })
					.style('stroke', function (d, i) { return color(i); });
			legend.append('text')
					.attr('class', 'legend')
					.attr('x', legendRectSize + legendSpacing)
					.attr('y', legendRectSize - legendSpacing)
					.text(function (d) { return d.label; });
	<?php } ?>
<?php } ?>
</script>
