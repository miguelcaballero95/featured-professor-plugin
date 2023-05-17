<?php

function relatedPostsHTML($id)
{
    $postsAboutProfessor = new WP_Query([
        'posts_per_page'    => -1,
        'post_type'         => 'post',
        'meta_query'        => [
            [
                'key'       => 'featuredProfessor',
                'compare'   => '=',
                'value'     => $id
            ]
        ]
    ]);

    ob_start();

    if ($postsAboutProfessor->found_posts) { ?>
        <p><?php the_title() ?> is mentioned in the following posts:</p>
        <ul>
            <?php
            while ($postsAboutProfessor->have_posts()) {
                $postsAboutProfessor->the_post(); ?>
                <li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></li>
            <?php
            } ?>
        </ul>
<?php
    }

    wp_reset_postdata();

    return ob_get_clean();
}
