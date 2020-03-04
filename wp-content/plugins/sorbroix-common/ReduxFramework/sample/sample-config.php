<?php


if (!class_exists("Redux_Framework_sample_config")) {

    class Redux_Framework_sample_config {

        public $args = array();
        public $sections = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {
            // This is needed. Bah WordPress bugs.  ;)
            if ( defined('TEMPLATEPATH') && strpos( Redux_Helpers::cleanFilePath( __FILE__ ), Redux_Helpers::cleanFilePath( TEMPLATEPATH ) ) !== false) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);    
            }
        }

        public function initSettings() {

            if ( !class_exists("ReduxFramework" ) ) {
                return;
            }       
            
            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            // If Redux is running as a plugin, this will remove the demo notice and links
            //add_action( 'redux/plugin/hooks', array( $this, 'remove_demo' ) );
            // Function to test the compiler hook and demo CSS output.
            //add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2); 
            // Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
            // Change the arguments after they've been declared, but before the panel is created
            //add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
            // Change the default value of a field after it's been set, but before it's been useds
            //add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
            // Dynamically add a section. Can be also used to modify sections/fields
            add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        function compiler_action($options, $css) {
            //echo "<h1>The compiler hook has run!";
            //print_r($options); //Option values
            //print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )

            /*
              // Demo of how to use the dynamic CSS and write your own static CSS file
              $filename = dirname(__FILE__) . '/style' . '.css';
              global $wp_filesystem;
              if( empty( $wp_filesystem ) ) {
              require_once( ABSPATH .'/wp-admin/includes/file.php' );
              WP_Filesystem();
              }

              if( $wp_filesystem ) {
              $wp_filesystem->put_contents(
              $filename,
              $css,
              FS_CHMOD_FILE // predefined mode settings for WP files
              );
              }
             */
        }

        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => esc_html__('Section via hook', 'redux-framework-demo'),
                'desc' => esc_html__('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
            );

            return $sections;
        }

        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }


        function change_defaults($defaults) {
            $defaults['str_replace'] = "Testing filter hook!";

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2);
            }

            // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
            remove_action('admin_notices', array(ReduxFrameworkPlugin::get_instance(), 'admin_notices'));
        }

        public function setSections() {

            // Background Patterns Reader
            $sample_patterns_path = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns = array();

            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();

                    while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {

                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode(".", $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[] = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;

            ob_start();

            $ct = wp_get_theme();
            $this->theme = $ct;
            $item_name = $this->theme->get('Name');
            $tags = $this->theme->Tags;
            $screenshot = $this->theme->get_screenshot();
            $class = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(__('Customize &#8220;%s&#8221;', 'redux-framework-demo'), $this->theme->display('Name'));
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_html_e('Current theme preview', 'pontus'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_html_e('Current theme preview', 'pontus'); ?>" />
            <?php endif; ?>

                <h4>
            <?php echo $this->theme->display('Name'); ?>
                </h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(__('By %s', 'redux-framework-demo'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(__('Version %s', 'redux-framework-demo'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . esc_html__('Tags', 'redux-framework-demo') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
                <?php
                if ($this->theme->parent()) {
                    printf(' <p class="howto">' . esc_html__('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.', 'pontus') . '</p>', esc_html__('http://codex.wordpress.org/Child_Themes', 'redux-framework-demo'), $this->theme->parent()->display('Name'));
                }
                ?>

                </div>

            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            $sampleHTML = '';
            if (file_exists(dirname(__FILE__) . '/info-html.html')) {
                /** @global WP_Filesystem_Direct $wp_filesystem  */
                global $wp_filesystem;
                if (empty($wp_filesystem)) {
                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();
                }
                $sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__) . '/info-html.html');
            }
            
            // ACTUAL DECLARATION OF SECTIONS          

            $this->sections[] = array(
                'icon' => 'el-icon-cogs',
                'title' => __('General Settings', 'sorbroix'),
                'fields' => array(                  
                    array(
                        'id' => 'author_content',
                        'type' => 'textarea',
                        'title' => __('Author content', 'sorbroix'),
                        'subtitle' => __('', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Shtheme'
                    ),
                )
            );
            
            $this->sections[] = array(
                'icon' => ' el-icon-picture',
                'title' => __('Logo & Favicon Settings', 'sorbroix'),
                'fields' => array(       
                    array(
                        'id' => 'logo',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Logo Upload', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your logo.', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'logo2',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Logo Dark', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your logo.', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'favicon',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Favicon Upload', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Favicon.', 'sorbroix'),
                        'default' => ''
                    ),
                )
            );
            
            $this->sections[] = array(
                'icon' => 'el-icon-list',
                'title' => __('Blog Settings', 'sorbroix'),
                'fields' => array(
                    array(
                        'id' => 'blog_image',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Blog Image', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Image Background', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'blog_title',
                        'type' => 'text',
                        'title' => __('Blog title', 'sorbroix'),
                        'subtitle' => __('Input Blog title', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'sorbroix blog'
                    ),
                    array(
                        'id' => 'blog_subtitle',
                        'type' => 'text',
                        'title' => __('Blog Single title', 'sorbroix'),
                        'subtitle' => __('Input Blog Single title', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Blog Details'
                    ),
                    array(
                        'id' => 'blog_by',
                        'type' => 'text',
                        'title' => __('Text By', 'sorbroix'),
                        'subtitle' => __('Input Text By', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'By'
                    ), 
                    array(
                        'id' => 'blog_on',
                        'type' => 'text',
                        'title' => __('Text On', 'sorbroix'),
                        'subtitle' => __('Input Text On', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'On'
                    ), 
                    array(
                        'id' => 'blog_in',
                        'type' => 'text',
                        'title' => __('Text In', 'sorbroix'),
                        'subtitle' => __('Input Text In', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'In'
                    ), 
                    array(
                        'id' => 'blog_excerpt',
                        'type' => 'text',
                        'title' => __('Blog custom excerpt leng', 'sorbroix'),
                        'subtitle' => __('Input Blog custom excerpt leng', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => '50'
                    ),
                    array(
                        'id' => 'read_more',
                        'type' => 'text',
                        'title' => __('Button Text For Post', 'sorbroix'),
                        'subtitle' => __('Input Button Text', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Read More'
                    ),
                 )
            ); 
            
            $this->sections[] = array(
                'icon' => 'el-icon-list',
                'title' => __('Portfolio Settings', 'sorbroix'),
                'fields' => array(
                    array(
                        'id' => 'port_image',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Portfolio Image', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Image Background', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'port_subtitle',
                        'type' => 'text',
                        'title' => __('Portfolio Single title', 'sorbroix'),
                        'subtitle' => __('Input Portfolio Single title', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Portfolio Details'
                    ),
                    array(
                        'id' => 'port_cus',
                        'type' => 'text',
                        'title' => __('Text Customer', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Customer :'
                    ), 
                    array(
                        'id' => 'port_cat',
                        'type' => 'text',
                        'title' => __('Text Category', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Category :'
                    ), 
                    array(
                        'id' => 'port_date',
                        'type' => 'text',
                        'title' => __('Text Date', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Date :'
                    ), 
                    array(
                        'id' => 'port_status',
                        'type' => 'text',
                        'title' => __('Text Status', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Status :'
                    ), 
                    array(
                        'id' => 'port_demo',
                        'type' => 'text',
                        'title' => __('Text Live demo', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Live demo :'
                    ), 
                    array(
                        'id' => 'port_tags',
                        'type' => 'text',
                        'title' => __('Text Tags', 'sorbroix'),
                        'subtitle' => __('Input Text on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Tags :'
                    ), 
                    array(
                        'id' => 'port_template',
                        'type' => 'editor',
                        'title' => __('Text Template', 'sorbroix'),
                        'subtitle' => __('Input Template on Portfolio', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => ''
                    ), 
                    array(
                        'id' => 'ser_image',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Service Image', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Image Background', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'ser_title',
                        'type' => 'text',
                        'title' => __('Services title', 'sorbroix'),
                        'subtitle' => __('Input Services title', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'We Offer Services'
                    ),
                    array(
                        'id' => 'ser_subtitle',
                        'type' => 'text',
                        'title' => __('Services subtitle', 'sorbroix'),
                        'subtitle' => __('Input Services subtitle', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain.'
                    ),
                    array(
                        'id' => 'ser_template',
                        'type' => 'editor',
                        'title' => __('Text Template', 'sorbroix'),
                        'subtitle' => __('Input Template on Services', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => ''
                    ), 
                 )
            ); 

            $this->sections[] = array(
                'icon' => ' el-icon-picture',
                'title' => __('Page Settings', 'sorbroix'),
                'fields' => array(       
                    array(
                        'id' => 'page_image',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('Page Image', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Background Image on Page Default.', 'sorbroix'),
                        'default' => ''
                    ),
                    array(
                        'id' => 'page_subtitle',
                        'type' => 'text',
                        'title' => __('Page subtitle', 'sorbroix'),
                        'subtitle' => __('Input Page subtitle', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.'
                    ),
                )
            );
            $this->sections[] = array(
                'icon' => 'el-icon-graph',
                'title' => __('404 Settings', 'sorbroix'),
                'fields' => array(
                    array(
                        'id' => '404_image',
                        'type' => 'media',
                        'url' => true,
                        'title' => __('404 Image', 'sorbroix'),
                        'compiler' => 'true',
                        //'mode' => false, // Can be set to false to allow any media type, or can also be set to any mime type.
                        'desc' => __('', 'sorbroix'),
                        'subtitle' => __('Upload your Image Background', 'sorbroix'),
                        'default' => ''
                    ),
                     array(
                        'id' => '404_title',
                        'type' => 'text',
                        'title' => __('404 Title', 'sorbroix'),
                        'subtitle' => __('Input 404 Title', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => '404 Error'
                    ),  
                    array(
                        'id' => '404_subtitle',
                        'type' => 'textarea',
                        'title' => __('404 subtitle', 'sorbroix'),
                        'subtitle' => __('Input 404 subtitle', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Oops! 404.This Server Page not be found.'
                    ), 
                    array(
                        'id' => '404_desc',
                        'type' => 'textarea',
                        'title' => __('404 description', 'sorbroix'),
                        'subtitle' => __('Input 404 description', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Id pri consul aeterno petentium. Vivendo abhorreant et vim, et quot persecuti mel.
          <br> Libris hendrerit ex sea. Duo legere evertitur an, pri hinc doctus definitiones an, vix id dicam putent. Ius ornatus instructior in.'
                    ), 
                    array(
                        'id' => '404_text',
                        'type' => 'text',
                        'title' => __('404 Text Button', 'sorbroix'),
                        'subtitle' => __('Input 404 Button', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'Home Page'
                    ), 
                               
                 )
            );

            $this->sections[] = array(
                'icon' => ' el-icon-credit-card',
                'title' => __('Header Settings', 'sorbroix'),
                'fields' => array(  
                    array(
                        'id'       => 'header_select',
                        'type'     => 'select',
                        'title'    => __('Select Type Header', 'sorbroix'), 
                        'subtitle' => __('Select Type Header on All pages , post...', 'sorbroix'),
                        'desc'     => __('This is the description field, again good for additional info.', 'sorbroix'),
                        // Must provide key => value pairs for select options
                        'options'  => array(
                            '1' => __( 'Header 1', 'sorbroix' ),
                            '2' => __( 'Header 2', 'sorbroix' ),
                            '3' => __( 'Header 3', 'sorbroix' ),
                            '4' => __( 'Header 4', 'sorbroix' ),
                            '5' => __( 'Header 5', 'sorbroix' )
                        ),
                        'default'  => '1',
                    ),
                    array(
                        'id' => 'header_form',
                        'type' => 'textarea',
                        'title' => __('Header Form', 'sorbroix'),
                        'subtitle' => __('Header Form Appointment', 'sorbroix'),
                        'default' => '',
                    ), 
                    array(
                        'id' => 'header_text1',
                        'type' => 'text',
                        'title' => __('Header Text 1', 'sorbroix'),
                        'subtitle' => __('Text 1 Pull Left on Header 1, 2 ,4', 'sorbroix'),
                        'default' => '<a href="#"><i class="flaticon-phone-receiver"></i>1800 879 5247</a>',
                    ), 
                    array(
                        'id' => 'header_text2',
                        'type' => 'text',
                        'title' => __('Header Text 2', 'sorbroix'),
                        'subtitle' => __('Text 2 Pull Left on Header 1, 2 ,4', 'sorbroix'),
                        'default' => '<a href="#"><i class="flaticon-send"></i>Free Enquiry</a>',
                    ), 
                    array(
                        'id' => 'header_link1',
                        'type' => 'text',
                        'title' => __('Header Link social 1', 'sorbroix'),
                        'subtitle' => __('Header Link social 1', 'sorbroix'),
                        'default' => '#', 'sorbroix',
                    ), 
                    array(
                        'id' => 'header_icon1',
                        'type' => 'text',
                        'title' => __('Header Icon Social 1', 'sorbroix'),
                        'subtitle' => __('Header Icon Social 1', 'sorbroix'),
                        'default' => 'fa fa-facebook',
                    ),
                    array(
                        'id' => 'header_link2',
                        'type' => 'text',
                        'title' => __('Header Link social 2', 'sorbroix'),
                        'subtitle' => __('Header Link social 2', 'sorbroix'),
                        'default' => '#', 'sorbroix',
                    ), 
                    array(
                        'id' => 'header_icon2',
                        'type' => 'text',
                        'title' => __('Header Icon Social 2', 'sorbroix'),
                        'subtitle' => __('Header Icon Social 2', 'sorbroix'),
                        'default' => 'fa fa-twitter',
                    ),
                    array(
                        'id' => 'header_link3',
                        'type' => 'text',
                        'title' => __('Header Link social 3', 'sorbroix'),
                        'subtitle' => __('Header Link social 3', 'sorbroix'),
                        'default' => '#', 'sorbroix',
                    ), 
                    array(
                        'id' => 'header_icon3',
                        'type' => 'text',
                        'title' => __('Header Icon Social 3', 'sorbroix'),
                        'subtitle' => __('Header Icon Social 3', 'sorbroix'),
                        'default' => 'fa fa-google-plus',
                    ),
                    array(
                        'id' => 'header_link4',
                        'type' => 'text',
                        'title' => __('Header Link social 4', 'sorbroix'),
                        'subtitle' => __('Header Link social 4', 'sorbroix'),
                        'default' => '#', 'sorbroix',
                    ), 
                    array(
                        'id' => 'header_icon4',
                        'type' => 'text',
                        'title' => __('Header Icon Social 4', 'sorbroix'),
                        'subtitle' => __('Header Icon Social 4', 'sorbroix'),
                        'default' => 'fa fa-linkedin',
                    ),
                    array(
                        'id' => 'header_lang',
                        'type' => 'textarea',
                        'title' => __('Header Text Language', 'sorbroix'),
                        'subtitle' => __('Header Text Language', 'sorbroix'),
                        'default' => '<option>EN</option>
                            <option>BN</option>
                            <option>IN</option>',
                    ),
                    array(
                        'id' => 'header3_text',
                        'type' => 'text',
                        'title' => __('Header 3 Text Top bar', 'sorbroix'),
                        'subtitle' => __('Text Address on Home 3', 'sorbroix'),
                        'default' => '<a href="#"><i class="icon icon-Pointer"></i>562, Mallin Street, New Youk, 100 254</a>',
                    ), 
                    array(
                        'id' => 'header3_icon1',
                        'type' => 'text',
                        'title' => __('Header Icon Info 1', 'sorbroix'),
                        'subtitle' => __('Header Icon Info 1 on Header 3', 'sorbroix'),
                        'default' => 'icon icon-Pointer',
                    ),
                    array(
                        'id' => 'header3_text1',
                        'type' => 'text',
                        'title' => __('Header Text Info 1', 'sorbroix'),
                        'subtitle' => __('Text Info 1 on Header 3', 'sorbroix'),
                        'default' => '<h4>13005 Greenville Avenue</h4>                                          
                                <p>California, TX 70240</p>    ',
                    ), 
                    array(
                        'id' => 'header3_icon2',
                        'type' => 'text',
                        'title' => __('Header Icon Info 2', 'sorbroix'),
                        'subtitle' => __('Header Icon Info 2 on Header 3', 'sorbroix'),
                        'default' => 'icon icon-Phone2',
                    ),
                    array(
                        'id' => 'header3_text2',
                        'type' => 'text',
                        'title' => __('Header Text Info 2', 'sorbroix'),
                        'subtitle' => __('Text Info 2 on Header 3', 'sorbroix'),
                        'default' => '<h4>+(1800) 456 7890</h4>                 
                                <p>Toll Free</p>',
                    ), 
                    array(
                        'id' => 'header3_icon3',
                        'type' => 'text',
                        'title' => __('Header Icon Info 3', 'sorbroix'),
                        'subtitle' => __('Header Icon Info 3 on Header 3', 'sorbroix'),
                        'default' => 'icon icon-Timer',
                    ),
                    array(
                        'id' => 'header3_text3',
                        'type' => 'text',
                        'title' => __('Header Text Info 3', 'sorbroix'),
                        'subtitle' => __('Text Info 3 on Header 3', 'sorbroix'),
                        'default' => '<h4>Mon - Sat 9.00 - 19.00</h4>                                               
                                <p>Sunday CLOSED</p>',
                    ), 
                    array(
                        'id' => 'header3_text4',
                        'type' => 'text',
                        'title' => __('Header Text Phone on Menu', 'sorbroix'),
                        'subtitle' => __('Text Phone on Header 3', 'sorbroix'),
                        'default' => '<a href="#">1800.568.3248</a>',
                    ), 
                    array(
                        'id' => 'header_button',
                        'type' => 'text',
                        'title' => __('Header Button', 'sorbroix'),
                        'subtitle' => __('Text Button on Header 4', 'sorbroix'),
                        'default' => '<a href="#" class="submit learn_btn">Requet call back</a>',
                    ), 


                )
            );
            $this->sections[] = array(
                'icon' => 'el-icon-graph',
                'title' => __('Form Settings', 'sorbroix'),
                'fields' => array(
                     array(
                        'id' => 'form_email',
                        'type' => 'text',
                        'title' => __('Form email', 'sorbroix'),
                        'subtitle' => __('Form email', 'sorbroix'),
                        'desc' => __('', 'sorbroix'),
                        'default' => 'info@domain.com'
                    ),                 
                 )
            );
            $this->sections[] = array(
                'icon' => 'el-icon-website',
                'title' => __('Styling Options', 'sorbroix'),
                'fields' => array(
                    array(
                        'id' => 'chosen-color',
                        'type' => 'checkbox',
                        'title' => __('Enable edit color', 'sorbroix'),
                        'subtitle' => '',
                        'desc' => '',
                        'default' => '0'// 1 = on | 0 = off
                    ),
                    array(
                        'id' => 'main-color-1',
                        'type' => 'color',
                        'title' => __('Theme Main Color 1', 'sorbroix'),
                        'subtitle' => __('Pick the main color for the theme (default: #ffe79b).', 'sorbroix'),
                        'default' => '#2c3e50',
                        'validate' => 'color',
                    ),
                    array(
                        'id' => 'main-color-2',
                        'type' => 'color',
                        'title' => __('Theme Main Color 2', 'sorbroix'),
                        'subtitle' => __('Pick the main color for the theme (default: #ffe79b).', 'sorbroix'),
                        'default' => '#2c3e50',
                        'validate' => 'color',
                    ),
                    array(
                        'id' => 'body-font2',
                        'type' => 'typography',
                        'output' => array('body'),
                        'title' => __('Body Font', 'sorbroix'),
                        'subtitle' => __('Specify the body font properties.', 'sorbroix'),
                        'google' => true,
                        'default' => array(
                            'color' => '#333',
                        ),
                    ),
                     array(
                        'id' => 'custom-css',
                        'type' => 'ace_editor',
                        'title' => __('CSS Code', 'sorbroix'),
                        'subtitle' => __('Paste your CSS code here.', 'sorbroix'),
                        'mode' => 'css',
                        'theme' => 'monokai',
                        'desc' => 'Possible modes can be found at <a href="http://ace.c9.io" target="_blank">http://ace.c9.io/</a>.',
                        'default' => "#header{\nmargin: 0 auto;\n}"
                    ),
                )
            );

        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id' => 'redux-opts-1',
                'title' => esc_html__('Theme Information 1', 'pontus'),
                'content' => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'pontus')
            );

            $this->args['help_tabs'][] = array(
                'id' => 'redux-opts-2',
                'title' => esc_html__('Theme Information 2', 'pontus'),
                'content' => esc_html__('<p>This is the tab content, HTML is allowed.</p>', 'pontus')
            );

            // Set the help sidebar
            $this->args['help_sidebar'] = esc_html__('<p>This is the sidebar content, HTML is allowed.</p>', 'pontus');
        }

        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name' => 'redux_demo', // This is where your data is stored in the database and also becomes your global variable name.
                'display_name' => $theme->get('Name'), // Name that appears at the top of your panel
                'display_version' => $theme->get('Version'), // Version that appears at the top of your panel
                'menu_type' => 'menu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu' => true, // Show the sections below the admin menu item or not
                'menu_title' => esc_html__('sorbroix Options', 'pontus'),
                'page' => esc_html__('sorbroix Options', 'pontus'),
                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => 'AIzaSyBM9vxebWLN3bq4Urobnr6tEtn7zM06rEw', // Must be defined to add google fonts to the typography module
                //'admin_bar' => false, // Show the panel pages on the admin bar
                'global_variable' => '', // Set a different name for your global variable other than the opt_name
                'dev_mode' => true, // Show the time the page took to load, etc
                'customizer' => true, // Enable basic customizer support
                // OPTIONAL -> Give you extra features
                'page_priority' => null, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent' => 'themes.php', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions' => 'manage_options', // Permissions needed to access the options panel.
                'menu_icon' => '', // Specify a custom URL to an icon
                'last_tab' => '', // Force your panel to always open to a specific tab (by id)
                'page_icon' => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
                'page_slug' => '_options', // Page slug used to denote the panel
                'save_defaults' => true, // On load save the defaults to DB before user clicks save or not
                'default_show' => false, // If true, shows the default value next to each field that is not the default value.
                'default_mark' => '', // What to print by the field's title if the value shown is default. Suggested: *
                // CAREFUL -> These options are for advanced use only
                'transient_time' => 60 * MINUTE_IN_SECONDS,
                'output' => true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag' => true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                //'domain'              => 'redux-framework', // Translation domain key. Don't change this unless you want to retranslate all of Redux.
                //'footer_credit'       => '', // Disable the footer credit of Redux. Please leave if you can help it.
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database' => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'show_import_export' => true, // REMOVE
                'system_info' => false, // REMOVE
                'help_tabs' => array(),
                'help_sidebar' => '', // esc_html__( '', $this->args['domain'] );            
            );


            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.     
            $this->args['share_icons'][] = array(
                'url' => 'https://github.com/ReduxFramework/ReduxFramework',
                'title' => 'Visit us on GitHub',
                'icon' => 'el-icon-github'
                    // 'img' => '', // You can use icon OR img. IMG needs to be a full URL.
            );
            $this->args['share_icons'][] = array(
                'url' => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
                'title' => 'Like us on Facebook',
                'icon' => 'el-icon-facebook'
            );
            $this->args['share_icons'][] = array(
                'url' => 'http://twitter.com/reduxframework',
                'title' => 'Follow us on Twitter',
                'icon' => 'el-icon-twitter'
            );
            $this->args['share_icons'][] = array(
                'url' => 'http://www.linkedin.com/company/redux-framework',
                'title' => 'Find us on LinkedIn',
                'icon' => 'el-icon-linkedin'
            );



            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace("-", "_", $this->args['opt_name']);
                }
                $this->args['intro_text'] = sprintf(__('<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'pontus'), $v);
            } else {
                $this->args['intro_text'] = esc_html__('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'pontus');
            }

            // Add content after the form.
            $this->args['footer_text'] = esc_html__('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'pontus');
        }

    }

    new Redux_Framework_sample_config();
}


if (!function_exists('redux_my_custom_field')):

    function redux_my_custom_field($field, $value) {
        print_r($field);
        print_r($value);
    }

endif;

if (!function_exists('redux_validate_callback_function')):

    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';
        /*
          do your validation

          if(something) {
          $value = $value;
          } elseif(something else) {
          $error = true;
          $value = $existing_value;
          $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }


endif;