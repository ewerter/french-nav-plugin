<?php
/**
 * Plugin Name: French Header Switcher
 * Description: Replaces header with French version when lang=fr
 * Version: 1.0
 * Author: Ewerton Barbosa
 */

if (!defined('ABSPATH')) exit;

class French_Header_Switcher {
    
    private $is_french = false;
    
    public function __construct() {
        // Early detection
        $this->detect_french_language();
        
        if ($this->is_french) {
            // Priority 0 - runs BEFORE everything
            add_action('wp_head', array($this, 'inject_french_header_css'), 0);
            
            // Inject header directly into body
            add_action('wp_body_open', array($this, 'inject_french_header_html'), 0);
            
            // Filter to hide default header
            add_filter('body_class', array($this, 'add_french_body_class'));
        }
        
        // Always add debug
        add_action('wp_footer', array($this, 'debug_info'), 999);
    }
    
    /**
     * Detect if current page is French
     */
    private function detect_french_language() {
        // Check WPML current language (most reliable)
        if (function_exists('icl_get_current_language')) {
            $current_lang = icl_get_current_language();
            if ($current_lang === 'fr') {
                $this->is_french = true;
                return;
            }
        }
        
        // Check URL parameter
        $url_lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
        if ($url_lang === 'fr') {
            $this->is_french = true;
            return;
        }
        
        // Check HTML lang attribute (parsed from document_head if available)
        $lang = get_bloginfo('language');
        if (strpos($lang, 'fr') === 0) {
            $this->is_french = true;
            return;
        }
        
        // Log detection
        error_log('French Header: Language detection - WPML: ' . (function_exists('icl_get_current_language') ? icl_get_current_language() : 'N/A') . ', URL: ' . $url_lang . ', Blog: ' . $lang);
    }
    
    /**
     * Inject CSS to hide default header and style French header
     */
    public function inject_french_header_css() {
        ?>
        <style id="french-header-styles">
            /* HIDE DEFAULT HEADER IMMEDIATELY */
            header.wp-block-template-part:not(.french-header-custom) {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            /* SHOW FRENCH HEADER */
            header.french-header-custom {
                display: block !important;
                visibility: visible !important;
            }

            /* French Header Styling */
            .french-header-custom {
                width: 100%;
                background-color: #d02478;
                margin: 0;
                padding: 0;
            }

            .french-header-custom .wp-block-group {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 15px 20px;
                max-width: 100%;
            }

            /* Logo - Left side */
            .french-header-custom .wp-block-site-logo {
                flex-shrink: 0;
                position: absolute;
                left: 15px;
                display: flex;
                align-items: center;
            }

            .french-header-custom .wp-block-site-logo img {
                max-width: 150px;
                width: auto;
                height: auto;
                display: block;
            }

            /* Text - Left side, next to logo */
            .french-header-custom .header-text {
                color: white;
                font-size: 18px;
                font-weight: 500;
                margin: 0;
                position: absolute;
                left: 150px;
                white-space: nowrap;
                flex-grow: 1;
            }

            /* Button - Far right, 20px from edge */
            .french-header-custom .header-lang-button {
                background-color: white;
                color: #d02478;
                border: none;
                padding: 10px 30px;
                border-radius: 25px;
                cursor: pointer;
                font-weight: 600;
                font-size: 16px;
                transition: all 0.3s ease;
                margin-left: auto;
                flex-shrink: 0;
                position: absolute;
                right: 15px;
            }

            .french-header-custom .header-lang-button:hover {
                background-color: #f0f0f0;
                transform: translateY(-2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }

            .french-header-custom .header-lang-button a {
                text-decoration: none;
                color: inherit;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .french-header-custom .wp-block-group {
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
                
                .french-header-custom .header-text {
                    display: none;
                }
                
                .french-header-custom .wp-block-site-logo {
                    margin-right: 0;
                    margin-bottom: 10px;
                    top: 60px;
                }
                
                .french-header-custom .header-lang-button {
                    margin-left: 0;
                    margin-right: 0;
                    top: 60px
                }
            }
        </style>

        <?php
    }
    
    /**
     * Inject French header HTML directly into body
     */
    public function inject_french_header_html() {
        $logo_url = 'https://mib.mzk.mybluehost.me/wp-content/uploads/2025/11/SCFP_pms227W.png';
        $english_url = home_url('/?lang=en');
        
        ?>
        <header class="french-header-custom wp-block-template-part">
            <div class="wp-block-group alignfull has-global-padding is-layout-constrained">
                <div class="wp-block-group is-content-justification-space-between is-nowrap is-layout-flex" style="margin-top:0; padding-top:var(--wp--preset--spacing--30); padding-bottom:var(--wp--preset--spacing--30);">
                    
                    <!-- Logo -->
                    <div class="is-default-size wp-block-site-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <img loading="lazy" 
                                 width="1000" 
                                 height="263" 
                                 src="<?php echo esc_url($logo_url); ?>" 
                                 alt="CUPE Support Long Term Care Workers" 
                                 decoding="async">
                        </a>
                    </div>
                    
                    <!-- Center Text -->
                    <p class="header-text">Soutenir les travailleurs des soins de longue durée</p>
                    
                    <!-- Language Button -->
                    <button class="header-lang-button">
                        <a href="<?php echo esc_url($english_url); ?>">Anglais</a>
                    </button>
                    
                </div>
            </div>
        </header>
        <?php
    }
    
    /**
     * Add class to body for CSS targeting
     */
    public function add_french_body_class($classes) {
        $classes[] = 'french-language-active';
        return $classes;
    }
    
    /**
     * Debug information
     */
    public function debug_info() {
        ?>
        <script>
        console.log('=== French Header Switcher Debug v2.0 ===');
        console.log('Is French:', <?php echo $this->is_french ? 'true' : 'false'; ?>);
        console.log('WPML Lang:', '<?php echo esc_js(function_exists('icl_get_current_language') ? icl_get_current_language() : 'N/A'); ?>');
        console.log('URL Lang:', '<?php echo esc_js(isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'none'); ?>');
        console.log('Blog Lang:', '<?php echo esc_js(get_bloginfo('language')); ?>');
        console.log('French Headers Found:', document.querySelectorAll('.french-header-custom').length);
        console.log('Default Headers Hidden:', document.querySelectorAll('header.wp-block-template-part:not(.french-header-custom)').length);
        console.log('Body Classes:', document.body.className);
        
        if (document.querySelectorAll('.french-header-custom').length > 0) {
            console.log('✅ French header successfully injected!');
        } else if (<?php echo $this->is_french ? 'true' : 'false'; ?>) {
            console.log('❌ French detected but header not found - check CSS');
        }
        </script>
        <?php
    }
}

// Initialize plugin
new French_Header_Switcher();

// Activation hook
register_activation_hook(__FILE__, function() {
    error_log('French Header Switcher v2.0 activated');
    flush_rewrite_rules();
});
