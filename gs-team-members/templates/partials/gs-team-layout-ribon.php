<?php

namespace GSTEAM;

if ( $display_ribbon !== 'on' ) return;

$is_featured      = get_post_meta( get_the_id(), '_gs_team_featured', true );
$ribon            = get_post_meta( get_the_id(), '_gs_ribon', true );


if ( $featured_badge === 'on' && $enable_featuring === 'on' && $is_featured ): ?>
    <div class="gs_team_featured_badge">
        <svg xmlns="http://www.w3.org/2000/svg" class="svg-icon" style="width: 1em; height: 1em;vertical-align: middle;fill: #dcbe5b;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1"><path d="M192 256a85.333333 85.333333 0 0 1 85.333333-85.333333h469.333334a85.333333 85.333333 0 0 1 85.333333 85.333333v575.274667a42.666667 42.666667 0 0 1-60.330667 38.826666l-242.005333-109.909333-20.906667-153.173333a8.490667 8.490667 0 0 1 6.442667 0l103.253333 41.984a8.533333 8.533333 0 0 0 11.690667-8.533334l-8.021333-111.104a8.533333 8.533333 0 0 1 1.962666-6.101333l71.850667-85.205333a8.533333 8.533333 0 0 0-4.522667-13.781334l-108.16-26.709333a8.533333 8.533333 0 0 1-5.162666-3.754667l-58.88-94.634666a8.533333 8.533333 0 0 0-14.464 0L445.909333 393.813333a8.533333 8.533333 0 0 1-5.12 3.754667l-108.202666 26.709333a8.533333 8.533333 0 0 0-4.522667 13.781334l71.850667 85.205333a8.533333 8.533333 0 0 1 1.962666 6.101333L393.813333 640.426667a8.533333 8.533333 0 0 0 11.733334 8.533333l103.210666-41.984-14.506666 153.173333-241.92 109.952a42.666667 42.666667 0 0 1-60.373334-38.826666V256z"/><path d="M494.336 760.192c11.264-5.12 24.064-5.12 35.328 0l-20.906667-153.173333-14.421333 153.173333z"/></svg>
    </div>
    <?php do_action( 'gs_team_after_member_featured_badge' ); ?>
<?php endif; ?>

<?php if ( !empty($ribon) ): ?>
    <div class="gs_team_ribbon"><?php echo esc_html( $ribon ); ?></div>
    <?php do_action( 'gs_team_after_member_ribbon' ); ?>
<?php endif; ?>