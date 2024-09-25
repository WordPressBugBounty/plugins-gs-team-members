<?php

namespace GSTEAM;

if ( $display_ribbon !== 'on' ) return;

$ribon = get_post_meta( get_the_id(), '_gs_ribon', true );

if ( !empty($ribon) ): ?>
    <div class="gs_team_ribbon"><?php echo esc_html( $ribon ); ?></div>
    <?php do_action( 'gs_team_after_member_ribbon' ); ?>
<?php endif; ?>