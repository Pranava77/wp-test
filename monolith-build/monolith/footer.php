<?php
/**
 * Site footer — shared on every page.
 *
 * @package monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer style="background:#14110c;color:#f3ede1;border-top:3px solid #14110c">
	<div style="max-width:1120px;margin:0 auto;padding:56px 32px 40px">
		<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:32px">
			<div style="max-width:360px">
				<?php if ( has_custom_logo() ) : ?>
					<div class="wfx-foot-logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<div style="font-family:'Archivo',sans-serif;font-weight:900;font-size:32px;letter-spacing:-.02em">MONOLITH</div>
				<?php endif; ?>
				<p style="font-size:15px;color:#bdb6a6;line-height:1.5;margin:12px 0 0">Bold creative work with a pulse. Brand, web, product &amp; motion — crafted by hand, delivered fast.</p>
			</div>
			<div style="display:flex;gap:56px;flex-wrap:wrap">
				<div>
					<div style="font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.12em;color:#6b6557;margin-bottom:14px">PAGES</div>
					<div style="display:flex;flex-direction:column;gap:10px;font-size:15px">
						<a class="wfx-a" style="color:#f3ede1" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<a class="wfx-a" style="color:#f3ede1" href="<?php echo esc_url( home_url( '/work/' ) ); ?>">Work</a>
						<a class="wfx-a" style="color:#f3ede1" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
						<a class="wfx-a" style="color:#f3ede1" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>
					</div>
				</div>
				<div>
					<div style="font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.12em;color:#6b6557;margin-bottom:14px">ELSEWHERE</div>
					<div style="display:flex;flex-direction:column;gap:10px;font-size:15px">
						<span style="cursor:pointer">Instagram</span>
						<span style="cursor:pointer">Behance</span>
						<span style="cursor:pointer">Dribbble</span>
					</div>
				</div>
				<div>
					<div style="font-family:'Space Mono',monospace;font-size:12px;letter-spacing:.12em;color:#6b6557;margin-bottom:14px">SAY HI</div>
					<a class="wfx-a" style="font-family:'Archivo',sans-serif;font-weight:800;font-size:18px;color:#f3ede1" href="mailto:hello@example.com">hello@example.com</a>
				</div>
			</div>
		</div>
		<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;border-top:1px solid #2e2a22;margin-top:40px;padding-top:18px;font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.08em;color:#6b6557">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> MONOLITH — ALL RIGHTS RESERVED</span>
			<span>NEW YORK, NY</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
