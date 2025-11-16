<?php
  $course_id = get_the_ID();
  $required_product_id = get_post_meta($course_id, 'rs_lms_course_product', true);

  //If not logged in -> redirect
  if(!is_user_logged_in() || !$required_product_id){
    wp_redirect(home_url());
    exit;
  }

  $user_id = get_current_user_id();
  $has_access = false;

  if(class_exists('WooCommerce')){
    $orders = wc_get_orders([
      'customer_id' => $user_id,
      'status' => ['completed'],
      'limit' => -1,
      'return' => 'ids',
    ]);
  }

  if($orders){
    foreach($orders as $order_id){
      $order = wc_get_order($order_id);
      foreach($order->get_items() as $item){
        $product_id = $item->get_product_id();
        if($product_id == $required_product_id){
          $has_access = true;
          break 2;
        }
      }
    }
  }

  if(!$has_access){
    wp_redirect(home_url());
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Interactive Course Player</title>
    <?php 
      wp_head();
      the_post() 
    ?>
  </head>

  <body class="bg-gray-100 text-gray-800">
    <!-- Top Admin Bar -->
    <div id="admin-bar"
      class="w-full bg-black text-white flex items-center justify-between px-4 py-2 text-sm fixed top-0 left-0 z-50"
      style="height: 44px">
      <div class="flex items-center space-x-10 md:space-x-0">
        <!-- hamburger icon  -->
        <button id="hamburger-btn"
          class="fixed mr-2 left-4- z-50- bg-white text-white border border-gray-200 rounded-md p-1 shadow-lg- flex items-center justify-center md:hidden"
          aria-label="Open sidebar" style="display: none">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000" class="bi bi-list"
            viewBox="0 0 16 16">
            <path fill-rule="evenodd"
              d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
          </svg>
        </button>
        <span class="font-bold text-lg tracking-wide"><?= get_bloginfo(); ?></span>
      </div>
      <div class="flex items-center space-x-6">
        <span id="admin-bar-progress" class="flex items-center"><i class="fas fa-chart-line mr-1"></i> <span id="admin-bar-percentage">0%</span> Complete</span>
        <span id="admin-bar-username" class="flex items-center"><i class="fas fa-user-circle mr-1"></i>
        <?php
        $current_user = wp_get_current_user();
        echo esc_html( $current_user->display_name );
        ?>
        </span>
        <button id="theme-toggle-btn" aria-label="Toggle light/dark mode"
          class="ml-4 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded px-2 py-1 flex items-center">
          <span id="theme-toggle-icon" class="fa fa-moon"></span>
        </button>
      </div>
    </div>

    <!-- <div class="flex min-h-screen bg-white"> -->
    <div class="dashboard">
      <!-- Left Sidebar -->
      <aside id="sidebar"
        class="w-full md:w-1/3 lg:w-1/4 xl:w-1/5 h-full flex flex-col border-r border-gray-200 shadow-lg relative"
        style="min-width: 180px; max-width: 600px">
        <div id="sidebar-resizer" class="bar-resizer cursor-col-resize bg-blue-200 w-2 h-10"></div>
        <div class="sidebar-content">
          <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div>
              <h1 class="text-xl font-bold text-gray-900"><?= the_title(); ?></h1>
              <p class="text-sm text-gray-500"><?= get_the_excerpt(); ?></p>
            </div>
            <button id="sidebar-close-btn"
              class="md:hidden ml-2 p-2 rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
              aria-label="Close sidebar" style="display: none">
              <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
          <div class="flex-grow overflow-y-auto sidebar-scrollbar">
            <!-- Chapters and Episodes -->
            <div id="course-outline">
              <?php
                $course_id = get_the_ID();
                $chapter_meta = get_post_meta($course_id, 'rs_lms_course_chapters', true);
                $chapter_list = $chapter_meta;
                $current_user_id = get_current_user_id();
                $user_bookmarks = get_user_meta($current_user_id, 'rs_lms_bookmarks', true);
                $user_completed = get_user_meta($current_user_id, 'rs_lms_completed', true); 


                if(!empty($chapter_list)){
                  $chapter_number = 1;
                  foreach($chapter_list as $chapter){
                    $chapter_id = isset($chapter['chapter_id']) ? $chapter['chapter_id'] : false;
                    $chapter_post = get_post($chapter_id);
                    if(!$chapter_post)
                      continue;
                    $chapter_title = get_the_title($chapter_post);
                    $episodes = get_post_meta($chapter_id, 'rs_lms_chapter_episodes', true);
                    $episode_list = $episodes
                    ?>
                    <div class="chapter border-b border-gray-200 <?php if($chapter_number === 1)
                      echo ' open'?>">
                      <div class="chapter-header flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800"><?php echo 'Chapter ' . $chapter_number . ': ' . esc_html($chapter_title); ?></h2>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                      </div>
                      <div class="chapter-content">
                        <ul class="episodes-list">
                          <?php
                            $ep_num = 1;
                            foreach($episode_list as $ep){
                              $ep_title = isset($ep['title']) ? $ep['title'] : '';
                              $video_type = isset($ep['video_type']) ? $ep['video_type'] : '';
                              $video_url = isset($ep['video_url']) ? $ep['video_url'] : '';
                              $length = isset($ep['length']) ? $ep['length'] : '';
                              $note_link = isset($ep['note_link']) ? $ep['note_link'] : ''; 
                              $resource_download = isset($ep['resource_download']) ? $ep['resource_download'] : false;
                              $video_types_as_video = ['youtube', 'vimeo', 'wistia', 'self_hosted'];
                              $data_type = in_array($video_type, $video_types_as_video) ? 'video' : 'text';
                              $embed_url = $video_url;
                              $state_key = sprintf('%d:%d', $chapter_id, $ep_num);
                              $is_bookmarked = is_array($user_bookmarks) && in_array($state_key, $user_bookmarks, true);
                              $is_completed = is_array($user_completed) && in_array($state_key, $user_completed, true);
                              if($video_type=='youtube' && !empty($video_url)){
                                // Extract video ID from various YouTube URL formats
                                if (preg_match('~(?:youtu.be/|youtube.com/(?:watch\?v=|embed/|v/|shorts/))([\w-]{11})~', $video_url, $matches)) {
                                    $video_id = $matches[1];
                                    $embed_url = 'https://www.youtube.com/embed/' . $video_id;
                                }
                              }
                              if($video_type=='vimeo' && !empty($video_url)){
                                // Extract Vimeo video ID from URL
                                if (preg_match('~vimeo.com/(?:video/)?(\d+)~', $video_url, $matches)) {
                                    $vimeo_id = $matches[1];
                                    $embed_url = 'https://player.vimeo.com/video/' . $vimeo_id;
                                }
                              }
                              if ($video_type == 'self_hosted' && !empty($video_url)) {
                                  // Use internal player page that renders Video.js for the self-hosted source
                                  $player_path = plugin_dir_url(__FILE__) . 'video-player.php';
                                  $embed_url = add_query_arg([
                                      'chapter_id' => $chapter_id,
                                      'ep' => $ep_num,
                                      //'autoplay' => '1',
                                  ], $player_path);
                              }
                              ?>
                              <li class="episode flex items-start p-4 cursor-pointer hover:bg-blue-50 transition-colors duration-200 <?php echo $is_bookmarked ? ' bookmarked' : ''; ?> <?php echo $is_completed ? ' completed' : ''; ?>" data-content-type="<?php echo esc_attr($data_type); ?>" <?php if ($data_type === 'video') { ?>data-content-src="<?php echo esc_url($embed_url); ?>" <?php } ?> data-note-link="<?php echo esc_attr($note_link ? $note_link : ''); ?>" data-video-type="<?php echo esc_attr($video_type); ?>" data-resource-download="<?php echo esc_attr($resource_download ? $resource_download : ''); ?>" data-resource-url="<?php echo esc_attr($resource_download ? $resource_download : ''); ?>" data-chapter-id="<?php echo esc_attr($chapter_id);?>" data-ep="<?php echo esc_attr($ep_num);?>">
                                <span class="text-lg font-bold text-blue-600 mr-4"><?php echo $ep_num; ?></span>
                                <div class="flex-grow">
                                    <h3 class="font-medium text-gray-800"><?php echo esc_html($ep_title); ?></h3>
                                    <?php if ($length) { ?><span class="text-sm text-gray-500"><?php echo esc_html($length); ?></span><?php } ?>
                                </div>
                                <i class="<?php echo $is_bookmarked ? 'fas text-blue-600' : 'far'; ?> fa-bookmark text-gray-400 hover:text-blue-600 ml-4 mt-1"></i>
                              </li>
                              <?php
                              $ep_num++;
                            }
                          ?>
                        </ul>
                      </div>
                    </div>  
                    <?php
                    $chapter_number++;
                  }
                }else{
                      echo '<p class="p-4 text-red-500">No chapters found for this course.</p>';
                }
              ?>
            </div>
          </div>
        </div>
      </aside>
      <!-- Main Content -->
      <!-- <main class="flex-grow h-full flex flex-col bg-gray-50"> -->
      <main class="main-content overflow-y-hidden">
        <div id="content-display-wrapper" class="flex-grow overflow-y-auto">
          <div id="content-display">
            <!-- Content will be loaded here -->
          </div>
        </div>
        <div
          class="flex flex-col md:flex-row items-center justify-between p-4 border-t border-gray-200 bg-white md:sticky md:bottom-0 z-40">
          <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <button id="prev-btn"
              class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"><i
                class="fas fa-arrow-left mr-2"></i>Previous</button>
            <button id="next-btn"
              class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">Next<i
                class="fas fa-arrow-right ml-2"></i></button>
          </div>
          <div class="flex items-center space-x-2 md:space-x-4">
            <button id="complete-btn"
              class="px-4 py-2 rounded-lg border border-green-500 text-green-600 hover:bg-green-50 font-semibold transition-colors duration-200"><i
                class="far fa-check-circle mr-2"></i>Mark as Completed</button>
            <button id="bookmark-btn"
              class="px-4 py-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-200"><i
                class="far fa-bookmark mr-2"></i>Bookmark</button>
            <a id="download-btn" href="#"
              class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white font-semibold transition-colors duration-200"><i
                class="fas fa-download mr-2"></i>Download Resource</a>
          </div>
        </div>
      </main>
    </div>

    <!-- Templates for rich text content -->
    <template id="episode-2-content">
      <div class="rich-content p-4 md:p-8">
        <h2>Core Principles of Good Design</h2>
        <p>This episode covers the fundamental principles of design. We will explore concepts like balance, contrast,
          hierarchy, and repetition. Understanding these principles is crucial for creating visually appealing and
          effective designs.</p>
        <img src="https://placehold.co/800x400/e2e8f0/475569?text=Design+Principle+Diagram"
          alt="Diagram of design principles" />
        <p>For more in-depth reading, check out <a href="#" target="_blank">this article on Smashing Magazine</a>. It
          provides excellent examples.</p>
        <p>Here is an example of how you might structure your HTML for a card component:</p>
        <pre><code class="language-html">&lt;div class="card"&gt;
    &lt;img src="image.jpg" alt="Card Image"&gt;
    &lt;div class="card-content"&gt;
      &lt;h3&gt;Card Title&lt;/h3&gt;
      &lt;p&gt;Some descriptive text about the card.&lt;/p&gt;
    &lt;/div&gt;
  &lt;/div&gt;</code></pre>
      </div>
    </template>
    <template id="episode-3-content">
      <div class="rich-content p-4 md:p-8">
        <h2>Advanced Topic 1: Typography</h2>
        <p>This is the text content for Advanced Topic 1. We will dive deep into typography, exploring font pairings, line
          height, and kerning to create beautiful and readable text layouts.</p>
      </div>
    </template>
    <template id="episode-4-content">
      <div class="rich-content p-4 md:p-8">
        <h2>UI Patterns: Cards and Lists</h2>
        <p>This episode covers common UI patterns such as cards and lists, and how to use them effectively in your
          designs.</p>
      </div>
    </template>
    <template id="episode-5-content">
      <div class="rich-content p-4 md:p-8">
        <h2>Advanced Topic 3: Color Theory</h2>
        <p>This episode explores the fundamentals of color theory and how to use color effectively in your designs.</p>
      </div>
    </template>
    <template id="episode-6-content">
      <div class="rich-content p-4 md:p-8">
        <h2>Advanced Topic 5: Accessibility</h2>
        <p>This episode covers accessibility best practices to ensure your designs are usable by everyone.</p>
      </div>
    </template>
    <template id="episode-7-content">
      <div class="rich-content p-4 md:p-8">
        <h2>Advanced Topic 7: Design Systems</h2>
        <p>This episode introduces design systems and how they help maintain consistency across products.</p>
      </div>
    </template>
    <!-- Example video notes template -->
    <template id="video-1-notes">
      <div class="rich-content">
        <h3 class="text-xl font-semibold mb-2">Video Notes & Resources</h3>
        <p class="mb-2">This video introduces the course and what you can expect to learn. Here are some key points and
          resources:</p>
        <ul class="list-disc pl-6 mb-4">
          <li>Understand the course structure and objectives.</li>
          <li>Meet your instructor and learn about their background.</li>
          <li>Overview of the tools and resources provided.</li>
        </ul>
        <img src="https://placehold.co/600x250/bae6fd/0c4a6e?text=Sample+Course+Diagram" alt="Sample Course Diagram"
          class="my-6 rounded-lg shadow" />
        <h4 class="font-semibold mt-4 mb-1">Sample Code</h4>
        <pre>
          <code class="language-javascript">// Example JavaScript code for a greeting
          function greet(name) {
              return `Hello, ${name}!`;
          }
          console.log(greet('World'));
          </code>
        </pre>
        <h4 class="font-semibold mt-4 mb-1">Downloadable Materials</h4>
        <ul class="list-disc pl-6 mb-4">
          <li><a href="#" class="text-blue-600 underline">Course Slides (PDF)</a></li>
          <li><a href="#" class="text-blue-600 underline">Sample Project Files</a></li>
        </ul>
        <h4 class="font-semibold mt-4 mb-1">Further Reading</h4>
        <ul class="list-disc pl-6">
          <li><a href="https://developer.mozilla.org/" target="_blank" class="text-blue-600 underline">MDN Web Docs</a>
          </li>
          <li><a href="https://www.smashingmagazine.com/" target="_blank" class="text-blue-600 underline">Smashing
              Magazine</a></li>
        </ul>
      </div>
    </template>
    <?php wp_footer(); ?>
  </body>
</html>