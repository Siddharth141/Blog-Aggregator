
<?php

function get_rss_feed_as_html($feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0, $cache_timeout = 7200, $cache_prefix = "/tmp/rss2html-")
{
    $result = "";
    // get feeds and parse items
    $rss = new DOMDocument();
    $cache_file = $cache_prefix . md5($feed_url);
    // load from file or load content
    if ($cache_timeout > 0 &&
        is_file($cache_file) &&
        (filemtime($cache_file) + $cache_timeout > time())) {
            $rss->load($cache_file);
    } else {
        $rss->load($feed_url);
        if ($cache_timeout > 0) {
            $rss->save($cache_file);
        }
    }
    $feed = array();
    foreach ($rss->getElementsByTagName('item') as $node) {
        $item = array (
            'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
            'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
            'content' => $node->getElementsByTagName('description')->item(0)->nodeValue,
            'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
            'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
        );
        $content = $node->getElementsByTagName('encoded'); // <content:encoded>
        if ($content->length > 0) {
            $item['content'] = $content->item(0)->nodeValue;
        }
        array_push($feed, $item);
    }
    // real good count
    if ($max_item_cnt > count($feed)) {
        $max_item_cnt = count($feed);
    }
    $result .= '<ul class="feed-lists">';
    for ($x=0;$x<$max_item_cnt;$x++) {
        $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $link = $feed[$x]['link'];
        $result .= '<li class="feed-item">';
        $result .= '<div class="feed-title"><strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong></div>';
        if ($show_date) {
            $date = date('l F d, Y', strtotime($feed[$x]['date']));
            $result .= '<small class="feed-date"><em>Posted on '.$date.'</em></small>';
        }
        if ($show_description) {
            $description = $feed[$x]['desc'];
            $content = $feed[$x]['content'];
            // find the img
            $has_image = preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);
            // no html tags
            $description = strip_tags(preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', "$1$3", $description), '');
            // whether cut by number of words
            if ($max_words > 0) {
                $arr = explode(' ', $description);
                if ($max_words < count($arr)) {
                    $description = '';
                    $w_cnt = 0;
                    foreach($arr as $w) {
                        $description .= $w . ' ';
                        $w_cnt = $w_cnt + 1;
                        if ($w_cnt == $max_words) {
                            break;
                        }
                    }
                    $description .= " ...";
                }
            }
            // add img if it exists
            if ($has_image == 1) {
                $description = '<img class="feed-item-image" src="' . $image['src'] .'" width="200" height="200"  /> <br>' . $description;
            }
            $result .= '<div class="feed-description">' . $description;
            $result .= ' <a href="'.$link.'" title="'.$title.'">Click here to continue reading </a>'.'</div>';
        }
        $result .= '</li><br>';
    }
    $result .= '</ul> <br>';
    return $result;
}

function output_rss_feed($feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0)
{
    echo get_rss_feed_as_html($feed_url, $max_item_cnt, $show_date, $show_description, $max_words);
}

?>



<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Blog Aggeregator/Home</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
       
        <style>
            body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

            .topnav {
            overflow: hidden;
            background-color: #333;
        }

            .topnav a {
            float: right;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

            .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

            .topnav a.active {
            background-color: rgb(221, 101, 54);
            color: white;
        }

            .layout--main {
                max-width: 1142px;
                height: 100%;
                min-height: calc(100vh - 212px);
                margin: 0px auto;
                margin-top: 0px;
                margin-right: auto;
                margin-bottom: 0px;
                margin-left: auto;
                padding: 60px 0px 0px;
                padding-top: 60px;
                padding-right: 0px;
                padding-bottom: 0px;
                padding-left: 0px;
        }
        .item-list {
            grid-template-columns: repeat(3, calc(33% - 8px));
        }

        body {
        background: #555;
        }

        .center {
        margin: auto;
        width: 50%;
        border: 5px solid rgb(221, 101, 54);
        padding: 10px;
        background-color:white ;
        }

        </style>
    </head>

    <body>
            
           
            <div class="topnav">
                <font color = orange   size = "6">
                 BLOGATOR
                </font>
                <a  href="entertainment.php">ENTERTAINMENT</a>
                <a  class="active" href="sports.php">SPORTS</a>
                <a  href="business.php">BUSINESS</a>
                <a  href="technology.php">TECHNOLOGY</a>
                <a   href="main1.php">HOME</a>
             </div>
             <div class ="center">
                    <?php
                        // output RSS feed to HTML
                        output_rss_feed('https://timesofindia.indiatimes.com/rssfeeds/4719148.cms', 20, true, true, 200);
                    ?>
            </div>
           
        </div>
    </body>
</html>

