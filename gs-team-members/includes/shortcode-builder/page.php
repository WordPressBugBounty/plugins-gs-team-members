<?php

$routes = [
	[
		'slug'  => '/',
		'title' => __('Shortcodes', 'gsteam')
	],
	[
		'slug'  => '/shortcode',
		'title' => __( 'Create New', 'gsteam' )
	],
	[
		'slug'  => '/preferences',
		'title' => __( 'Preferences', 'gsteam' )
	],
	[
		'slug'  => '/taxonomies',
		'title' => __( 'Taxonomies', 'gsteam' )
	],
	[
		'slug'  => '/bulk-import',
		'title' => __( 'Bulk Import', 'gsteam' )
	],
	[
		'slug'  => '/demo-data',
		'title' => __( 'Demo Data', 'gsteam' )
	],
	[
		'slug'  => '/import-export',
		'title' => __( 'Import Export', 'gsteam' )
	]
];

?>
<div class="app-container">
	<div class="main-container">		
		<div id="gs-team-shortcode-app">
			<header class="gs-team-header">
				<div class="gs-containeer-f">
					<div class="gs-roow">
						<div class="logo-area gs-col-xs-6 gs-col-sm-5 gs-col-md-3">
							<router-link to="/"><img src="<?php echo GSTEAM_PLUGIN_URI . '/assets/img/logo.svg'; ?>" alt="GS Team Members Logo"></router-link>
						</div>
						<div class="menu-area gs-col-xs-6 gs-col-sm-7 gs-col-md-9 text-right">
							<ul>
								<?php
								foreach($routes as $route) { ?>
									<router-link to=<?php echo esc_attr($route['slug']); ?> custom v-slot="{ isActive, href, navigate, isExactActive }">
										<li :class="[isActive ? 'router-link-active' : '', isExactActive ? 'router-link-exact-active' : '']">
											<a :href="href" @click="navigate" @keypress.enter="navigate" role="link"><?php echo esc_html($route['title']); ?></a>
										</li>
									</router-link>									
								<?php
								}
								?>								
							</ul>
						</div>
					</div>
				</div>
			</header>

			<div class="gs-team-app-view-container">
				<router-view :key="$route.fullPath"></router-view>
			</div>

		</div>		
	</div>
</div>