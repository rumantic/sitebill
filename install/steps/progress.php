<!-- #section:plugins/fuelux.wizard -->
										<div id="fuelux-wizard" data-target="#step-container">
											<!-- #section:plugins/fuelux.wizard.steps -->
											<ul class="wizard-steps">
<?php

foreach ($sidebar_menu as $step_id => $menu_item) {
	
?>

					<li <?php if ($step == $step_id) {?>class="active"<?php } elseif ($step > $step_id) {?>class="complete"<?php }?>>
							<span class="step"><i class="menu-icon <?php echo $menu_item['icon'];?>"></i></span>
							<span class="title"> <?php echo $menu_item['title'];?>
							</span>
					</li>

<?php 
}
?>
					
					

				</ul>	
	</div>
							<div class="hr hr-18 hr-double dotted"></div>
	

