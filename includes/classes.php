<?php

require plugin_dir_path(__file__) . 'views.php';
require plugin_dir_path(__file__) . 'helpers.php';

class MF_Import {

    public function run() {
        $this->admin_init();
        $this->load_scripts();
    }

    private function load_scripts() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('bootstrap', $this->plugin_url . '/public/css/bootstrap.min.css');
    }

    private function admin_init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page('JSON Import', 'Import', 'manage_options', 'mf_import', array($this, 'upload_form'));
    }

    public function upload_form() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->process_file();
        } else {
            $views = new MF_Views();
            return $views->dashboard();
        }
    }

    private function process_file() {
        try {
            $json = file_get_contents($_FILES['mf_file']['tmp_name']);
            $records = json_decode($json);
            $this->process_posts($records);
        } catch (Exception $ex) {
            $view = new MF_Views();
            return $view->error($ex->getMessage());
        }
    }

    private function process_posts($records) {
        $helpers = new MF_Helpers();
        try {
            if (empty($records)) {
                throw new Exception("There are no posts in the uploaded JSON File, please review the format.");
            }
            foreach ($records as $record) {
                echo '<pre>';
                //var_dump($record); 
                echo '</pre>';
                $post = array();
                $post["post_date"] = $helpers->setDate($record->date);
                $post["post_content"] = $record->content;
                $post["post_title"] = $record->title;
                if (!empty($record->excerpt)) {
                    $post["post_excerpt"] = $record->excerpt;
                }
                $post["post_status"] = $record->status;
                $post["post_type"] = $record->type;
                $post["post_category"] = $this->setCategories($record->categories);
                $post["meta_input"] = $record->meta;
                if(!empty($record->gallery)){
                    $images = array(); 
                    foreach($record->gallery as $g){
                        $images[] = $this->saveImage($g); 
                    }
                    $short_code = '[gallery columns="4" ids="'.implode(",", $images).'"]'; 
                    $post["post_content"] = $post["post_content"]." ".$short_code; 
                }
                if(!empty($record->sliders)){
                    $image = array(); 
                    foreach($record->sliders as $s){
                        $images[] = $this->saveImage($s); 
                    }
                    $short_code = '[vc_images_carousel images="'.implode(",", $images).'" img_size="full" speed="4000" autoplay="yes" hide_pagination_control="yes" scroll_fx="crossfade" min_items="1" max_items="1"]'; 
                    
                    $post["post_content"] = $short_code." ".$post["post_content"]; 
                }
                $post_id = wp_insert_post($post);
                if(!empty($record->featured_image)){
                    $this->saveImage($record->featured_image, $post_id, true); 
                }
            }
        } catch (Exception $ex) {
            $view = new MF_Views();
            return $view->error($ex->getMessage());
        }
    }

    function setCategories($categories) {
        $ids = array();
        if (!empty($categories)) {
            $parent = 0;
            foreach ($categories as $category) {
                if (is_array($category)) {
                    foreach ($category as $c) {
                        $id = wp_create_category($c, $parent);
                        $parent = $id;
                        $ids[] = $id;
                    }
                }
                if (is_string(($category))) {
                    $ids[] = wp_create_category($category);
                }
            }
        }
        return $ids;
    }

    function saveImage($image, $post_id = 0, $thumbnail = false) {
        if (!function_exists('wp_crop_image')) {
            include( ABSPATH . 'wp-admin/includes/image.php' );
        }
        try {
        // Add Featured Image to Post
        $image_url = $image->url; // Define the image URL here
        $image_name = $image->name;
        $upload_dir = wp_upload_dir(); // Set upload folder
        $image_data = file_get_contents($image_url); // Get image data
        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
        $filename = basename($unique_file_name); // Create image file name
// Check folder permission and define file location
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

// Create the image  file on the server
        file_put_contents($file, $image_data);

// Check image file type
        $wp_filetype = wp_check_filetype($filename, null);

// Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );


        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

        wp_update_attachment_metadata($attach_id, $attach_data);

        if ($thumbnail == true) {
            set_post_thumbnail($post_id, $attach_id);
        }

        return $attach_id;
        }
        catch(Exception $e){
            
        }
    }

}
