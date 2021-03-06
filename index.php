<?php    
    if (isset($_GET['init']) && !file_exists('mails.db'))
    {
        try
        {    
            $pdo = new PDO('sqlite:mails.db');
            $pdo->exec('CREATE TABLE mails (
                id   INTEGER     PRIMARY KEY,
                mail TEXT(2000)  NOT NULL,
                lang TEXT(10)    NOT NULL,
                date INTEGER(16) NOT NULL,
                ip   TEXT(80)    NOT NULL
            );');
            $pdo->exec('CREATE UNIQUE INDEX mails_idx ON mails(mail);');
        }
        catch (PDOException $e)
        {
            die ('DB Error');
        }
    }

    if (isset($_POST['email']))
    {
        // Anti-brute-force
        sleep(1);

        $email = $_POST['email'];
        $email_lang = isset($_POST['lang']) ? $_POST['lang'] : 'en';

        $ajax = isset($_POST['ajax']);

        function return_value($value, $ajax)
        {
            if ($ajax)
            {
                header('Content-Type: application/json');
                die('{"result":"' . $value . '"}');
            }
            else
            {
                header('Location:?' . $value);
                die();
            }
        }

        if (strpos($email, '@') === false)
        {
            return_value('ko-email', $ajax);
        }

        try
        {
            $pdo = new PDO('sqlite:mails.db');
            $ip = $_SERVER['REMOTE_ADDR'];

            $q = $pdo->prepare('SELECT COUNT(*) AS recent_actions FROM mails WHERE date > strftime("%s", "now", "-15 seconds") AND ip = ?');
            $q->execute([$ip]);
            $r = $q->fetch();

            if ($r && $r['recent_actions'] > 2)
            {
                return_value('ko-rate-limit', $ajax);
            }

            $q = $pdo->prepare('INSERT OR IGNORE INTO mails (mail, lang, date, ip) VALUES (?, ?, strftime("%s","now"), ?)');
            $q->execute([$email, $email_lang, $ip]);

            $q = $pdo->prepare('UPDATE mails SET lang = ?, ip = ? WHERE mail = ?');
            $q->execute([$email_lang, $ip, $email]);

            return_value('ok', $ajax);
        }
        catch (PDOException $e)
        {
            return_value('ko', $ajax);
        }
    }

    require_once('lang.php');

    $translations = [
        'fr' => [
            'page_title' => 'Drawesome Ninja!',
            'title' => 'Préparez vos crayons.',
            'description' => 'Drawesome Ninja réinvente le Pictionary en ligne.<br />Jouez, dessinez, devinez — en toute simplicité.',
            'description_opengraph' => 'Futur service interactif &amp; dynamique de Pictionary en ligne. Préparez vos crayons.',
            'invite_email' => 'Vous voulez être parmis les premiers à en profiter&nbsp;? Donnez-nous votre adresse&nbsp;!',
            'placeholder_email' => 'Entrez votre e-mail',
            'button_email' => 'Prévenez-moi',
            'button_email_success' => 'Merci !',
            'button_email_error' => 'Erreur :(',
            'button_email_error_email' => 'Entrez un courriel :)',
            'button_email_error_rate_limit' => 'Doucement ! Merci de patienter ~15 secondes',
            'twitter' => 'Compte Twitter',
            'twitter_account' => 'Drawesome_Ninja',
            'github' => 'Code source sur GitHub (on est open-source !)'
        ],
        'en' => [
            'page_title' => 'Drawesome Ninja!',
            'title' => 'Hold your pencils.',
            'description' => 'Drawesome Ninja reinvents online Pictionary.<br />Play, draw, guess — all in a simple, clear game interface.',
            'description_opengraph' => 'Futur interactive service to play online Pictionary. Hold your pencils.',
            'invite_email' => 'Want to be the first to enjoy it? Give us your e-mail address and we\'ll let you know when it\'s ready!',
            'placeholder_email' => 'Enter your e-mail',
            'button_email' => 'Let me know',
            'button_email_success' => 'Thanks!',
            'button_email_error' => 'Error :(',
            'button_email_error_email' => 'Please enter an email :)',
            'button_email_error_rate_limit' => 'Hey, slow down! Please wait ~15 seconds.',
            'twitter' => 'Twitter account',
            'twitter_account' => 'Drawesome_Ninja',
            'github' => 'Source code on GitHub (we\'re open source!)'
        ]
    ];

    $available_languages = array('en', 'fr');
    $default_language = "en";

    $lang = prefered_language($available_languages, $_SERVER["HTTP_ACCEPT_LANGUAGE"], $default_language);
    $t = $translations[$lang];

    $url = sprintf(
        "%s://%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME']
    );
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?=$t['page_title'] ?></title>

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" href="css/bulma.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />

        <link rel="icon" type="image/png" sizes="128x128" href="<?=$url; ?>/img/favicon.png" />

        <meta name="description" content="<?=$t['description_opengraph'] ?>" />

        <!-- Schema.org markup -->
        <meta itemprop="name" content="<?=$t['page_title'] ?>">
        <meta itemprop="description" content="<?=$t['description_opengraph'] ?>">
        <meta itemprop="image" content="<?=$url; ?>/img/opengraph.jpg">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?=$t['page_title'] ?>">
        <meta name="twitter:description" content="<?=$t['description_opengraph'] ?>">
        <meta name="twitter:creator" content="@<?=$t['twitter_account'] ?>">
        <meta name="twitter:image:src" content="<?=$url; ?>/img/opengraph.jpg">

        <!-- Open Graph data -->
        <meta property="og:title" content="<?=$t['page_title'] ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="<?=$url; ?>" />
        <meta property="og:image" content="<?=$url; ?>/img/opengraph.jpg" />
        <meta property="og:description" content="<?=$t['description_opengraph'] ?>" />
        <meta property="og:site_name" content="<?=$t['page_title'] ?>" />

        <?php if (isset($_GET['ok'])): ?>
            <meta http-equiv="refresh" content="5; url=/">
        <?php endif; ?>

        <!--
            Image de fond prise par Henry Söderlund
            https://www.flickr.com/photos/hrns/11293962213/in/photostream/
        -->
    </head>
    <body>
        <section class="hero is-dark is-fullheight">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <div class="column is-8 is-offset-2">
                        <h2 class="title"><?=$t['title'] ?></h2>
                        <p class="subtitle"><?=$t['description'] ?></p>
                        <aside>
                            <p class="mail-invite"><?=$t['invite_email'] ?></p>
                            <form class="field is-grouped" method="post" id="form-mail">
                                <input type="hidden" name="lang" value="<?=$lang ?>" />
                                <p class="control is-expanded">
                                    <input name="email" id="form-mail-email" class="input" placeholder="<?=$t['placeholder_email'] ?>" type="email" required />
                                </p>
                                <p class="control">
                                    <?php
                                        if (isset($_GET['ok']))
                                        {
                                            $class_button = 'is-success';
                                            $class_button_icon_orig = ' is-hidden';
                                            $class_button_icon_succ = '';
                                            $class_button_icon_errr = ' is-hidden';
                                            $text_button = $t['button_email_success'];
                                        }
                                        else if (isset($_GET['ko']) || isset($_GET['ko-email']) || isset($_GET['ko-rate-limit']))
                                        {
                                            $class_button = 'is-danger';
                                            $class_button_icon_orig = ' is-hidden';
                                            $class_button_icon_succ = ' is-hidden';
                                            $class_button_icon_errr = '';

                                            if (isset($_GET['ko']))
                                            {
                                                $text_button = $t['button_email_error'];
                                            }
                                            else if (isset($_GET['ko-rate-limit']))
                                            {
                                                $text_button = $t['button_email_error_rate_limit'];
                                            }
                                            else
                                            {
                                                $text_button = $t['button_email_error_email'];
                                            }
                                        }
                                        else
                                        {
                                            $class_button = 'is-info';
                                            $class_button_icon_orig = '';
                                            $class_button_icon_succ = ' is-hidden';
                                            $class_button_icon_errr = ' is-hidden';
                                            $text_button = $t['button_email'];
                                        }
                                    ?>
                                    <button
                                        class="button <?=$class_button ?>"
                                        type="submit"
                                        id="form-mail-submit"
                                        data-text-orig="<?=$t['button_email'] ?>"
                                        data-text-succ="<?=$t['button_email_success'] ?>"
                                        data-text-errr="<?=$t['button_email_error'] ?>"
                                        data-text-errm="<?=$t['button_email_error_email'] ?>"
                                        data-text-errl="<?=$t['button_email_error_rate_limit'] ?>">
                                        <span class="icon is-small<?=$class_button_icon_orig ?>" id="button-mail-icon-original" aria-hidden="true">
                                            <span class="fa fa-paint-brush"></span>
                                        </span>
                                        <span class="icon is-small<?=$class_button_icon_errr ?>" id="button-mail-icon-error" aria-hidden="true">
                                            <span class="fa fa-times"></span>
                                        </span>
                                        <span id="button-mail-text"><?=$text_button ?></span>
                                        <span class="icon is-small<?=$class_button_icon_succ ?>" id="button-mail-icon-success" aria-hidden="true">
                                            <span class="fa fa-check"></span>
                                        </span>
                                    </button>
                                </p>
                            </form>
                        </aside>
                        <aside class="external-links">
                            <a href="https://twitter.com/<?=$t['twitter_account'] ?>" class="icon is-medium" title="<?=$t['twitter'] ?>">
                                <span class="fa fa-birdsite fa-2x"></span>
                            </a>
                            <a href="https://github.com/zDevelopers/DrawesomeNinja" class="icon is-medium" title="<?=$t['github'] ?>">
                                <span class="fa fa-codesite-ghb fa-2x"></span>
                            </a>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        <script async type="text/javascript" src="js/js.js"></script> <!-- js js js js js -->

        <!-- Piwik -->
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//nsa.carrade.eu/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', '4']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        <noscript><p><img src="//nsa.carrade.eu/piwik.php?idsite=4&rec=1" style="border:0;" alt="" /></p></noscript>
        <!-- End Piwik Code -->
    </body>
</html>
