<?php
/**
 * @wordpress-plugin
 * Plugin Name:       IntelliContent
 * Plugin URI:        https://wordpress.org/plugins/intelli-content
 * Description:       Generate high-quality content using OpenAI's API.
 * Version:           1.0.0
 * Author:            Azhar Khan
 * Author URI:        https://www.azhark.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       intelli-content
 */

require_once (dirname(__FILE__) . '/includes/menu.php');
require_once (dirname(__FILE__) . '/includes/settings.php');


// Content generation page callback function
// Content generation page callback function
function intelli_content_generation_page() {
    ?>
    <div class="wrap">
        <h1>Content Generation</h1>
        <div class="content-generation-form">
            <form method="post" action="">
                <p><label for="keyword">Enter a keyword or topic:</label></p>
                <p><input type="text" id="keyword" name="keyword" value="" class="regular-text"></p>
                <p><button type="submit" class="button-primary" name="generate_content">Generate Content</button></p>
            </form>
        </div>
        <?php
        if (isset($_POST['generate_content'])) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $content = intelli_content_generate($keyword);
            ?>
            <div id="generatedContent" class="generated-content">
                <?php if (!empty( $_POST['generated_content']) ) { ?> <h2>Generated Content</h2> <? } ?>
                <div class="content-text"><?php echo $content; ?></div>
                <form method="post" action="">
                    <input type="hidden" name="generated_content" value="<?php echo esc_attr($content); ?>">
                    <input type="hidden" name="generated_title" value="<?php echo esc_attr($keyword); ?>">
                    <p>
                        <button type="submit" class="button-primary" name="insert_post">Insert Post</button>
                    </p>
                </form>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

function intelli_content_handle_form_submission() {
    // Handle form submission
    if (isset($_POST['insert_post'])) {
        $content = ($_POST['generated_content']);

        $extracted_data = extract_title_from_content($content);
        $title = $extracted_data['title'];
        $content = $extracted_data['content'];

        // Define an array to map block types to their respective tags
        $block_map = array(
            'paragraph' => 'wp:paragraph',
            'heading'   => 'wp:heading',
            'list'      => 'wp:list',
            'list_item' => 'wp:list-item',
            'blockquote'=> 'wp:quote',
        );

        // Split the content into paragraphs and process each paragraph
        $paragraphs = explode("\n", $content);
        $processed_content = '';
        foreach ($paragraphs as $paragraph) {
            // Determine the block type based on the content
            $block_type = 'paragraph'; // Default to paragraph block
            if (strpos($paragraph, '#') === 0) {
                $block_type = 'heading'; // Heading block if starts with #
            } elseif (strpos($paragraph, '*') === 0) {
                $block_type = 'list_item'; // List item block if starts with *
            } elseif (strpos($paragraph, '>') === 0) {
                $block_type = 'blockquote'; // Blockquote block if starts with >
            }

            // Get the corresponding Gutenberg block tag
            $block_tag = $block_map[$block_type];

            // Append the processed paragraph to the content
            if (!empty (trim($paragraph))) {
                $processed_content .= "<!-- {$block_tag} -->" . wpautop(trim($paragraph, '#*>' . PHP_EOL)) . "<!-- /{$block_tag} -->";
            }
        }

        // Create post object
        $post_data = array(
            'post_title'    => sanitize_text_field($title),
            'post_content'  => $processed_content,
            'post_status'   => 'draft',
            'post_type'     => 'post'
        );

        // Insert the post into the database
        $post_id = wp_insert_post($post_data);

        // Check if post was successfully inserted
        if ($post_id) {
            // Redirect to post edit screen
            wp_redirect(admin_url("post.php?action=edit&post=$post_id"));
            exit;
        } else {
            // Display error notice
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Error: Failed to insert post.</p></div>';
            });
        }
    }

}

add_action('admin_notices', 'intelli_content_handle_form_submission');


// Extract title from content and remove it from the content using regex
function extract_title_from_content($content) {
    // Match the first <h1> tag and extract its content
    if (preg_match('/<h1>(.*?)<\/h1>/', $content, $matches)) {
        $title = $matches[1]; // Extracted title
        $new_content = preg_replace('/<h1>.*?<\/h1>/', '', $content, 1); // Remove the first occurrence of <h1> tag
    } else {
        $title = ''; // No title found
        $new_content = $content; // Content remains unchanged
    }

    // Return title and new content
    return array('title' => $title, 'content' => $new_content);
}


function intelli_content_generate($keyword) {
    $api_key = get_option('intelli_content_api_key');
    $model = 'gpt-3.5-turbo-0613'; // Latest GPT-3.5 model
    $temperature = get_option('intelli_content_temperature', 0.7);
    $max_tokens = get_option('intelli_content_max_tokens', 1000);

    $endpoint = 'https://api.openai.com/v1/chat/completions'; // Use chat completions endpoint for chat models

    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    );

    $prompt = "Generate content about and also add a h1 heading as title on top of the article : $keyword";

    $data = array(
        'model' => $model,
        'max_tokens' => $max_tokens,
        'temperature' => $temperature,
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $prompt
            )
        )
    );

    $args = array(
        'body' => json_encode($data),
        'headers' => $headers,
        'timeout' => 30,
    );

    $response = wp_remote_post($endpoint, $args);

    if (is_wp_error($response)) {
        return "Error: Failed to connect to OpenAI API. Please try again later.";
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($response_code === 200 && isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        } elseif (isset($data['error']['message'])) {
            return "<p class='error-message'>". "Error: " . $data['error']['message'] . "</p>";
        } else {
            return "<p class='error-message'>Error: Failed to generate content. Please try again later.</p>";
        }
    }
}