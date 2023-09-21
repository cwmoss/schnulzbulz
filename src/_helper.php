<?php


function strip_tags_w_attributes($src, $allowed_tags = "", $attrs = null) {
    if ($allowed_tags) {
        $allowed = array_map(function ($t) {
            return "<$t>";
        }, explode(',', $allowed_tags));
        $allowed = '<' . join('><', explode(',', $allowed_tags)) . '>';
    }
    $src = strip_tags($src, $allowed);

    /*
       falls es eine whitelist erlaubter tags gibt,
       müssen bei **diesen** tags noch attribute entfernt werden
    */
    if ($allowed_tags) {
        #print "DOM--($allowed_tags)\n";
        if (!$attrs) $attrs = array();
        else $attrs = explode(',', $attrs);

        $dom = new DOMDocument;                 // init new DOMDocument
        $dom->loadHTML("<div>$src</div>", LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);                  // load HTML into it
        $xpath = new DOMXPath($dom);            // create a new XPath
        $nodes = $xpath->query('//*[@*]');
        foreach ($nodes as $node) {
            #print "node: ".$node->name."\n";
            if ($node->attributes) foreach (iterator_to_array($node->attributes) as $attr => $val) {
                #  print "attr? $attr\n";
                if (!in_array($attr, $attrs)) $node->removeAttribute($attr);
                else {
                    if ($attr == 'href' && preg_match('/javascript/i', $node->getAttribute($attr))) {
                        $node->removeAttribute($attr);
                    }
                }
            }
        }
        $src = $dom->saveHTML();
        $src = substr($src, 5, -6);   # <div></div> wieder entfernen
    }
    return $src;
}

/*
   clean input data
   copied from drupal sources
 */

/*
    htmlspecialchars wird hier nicht verwendet, da es bereits
    bei den form_helpern ausgeführt wird.
    zu beachten ist die ausführung in non-form feldern, wie in der
    übersichtsseite. dafür gibts die h() funktion
 */
function do_clean_input(&$inp) {
    $inp = drupal_validate_utf8($inp) ? strip_tags($inp) : '';
}

/*
   whitelist variante für admin interface
 */
function do_clean_input_wl(&$inp) {
    $whitelist = "<p><a><b><i><strong><em><br><h1><h2><h3><h4><h5><ul><li><img>";
    $inp = drupal_validate_utf8($inp) ? strip_tags_w_attributes($inp, $whitelist) : '';
}

/*
    bei schwachgeprüften werten, entquotes maskieren
 */
function h($inp = "") {
    return htmlspecialchars($inp, ENT_QUOTES);
}

function debug_js($k = null, $v = null) {
    static $vars = [];
    if (is_null($k) && is_null($v)) {
        return json_encode($vars);
    }

    $vars[$k] = $v;
}


/*
    leere strings in null konvertieren
    soll heissen: user macht keine angaben
 */
function null_if_empty($var) {
    if ($var === '') return null;
    return $var;
}

function drupal_validate_utf8($text) {
    if (strlen($text) == 0) {
        return TRUE;
    }
    return (preg_match('/^./us', $text) == 1);
}



/*
    vereinfachte gettext pluralfindung
 
    normalerweise:
    po:
 msgid "One message was deleted."
 msgid_plural "%d messages were deleted."
 msgstr[0] "Eine Nachrichte wurde gelöscht."
 msgstr[1] "%d Nachrichten wurden gelöscht."
 
    api: printf(ngettext("One message was deleted.", "%d messages were deleted.", $c)), $c);
 
    bei uns sind die msgids keine brauchbaren englischen sätze, sondern abgeleitet:
    po file:
 msgid "nachricht multi delete"
 msgid_plural "plural nachricht multi delete"
 msgstr[0] "Eine Nachrichte wurde gelöscht."
 msgstr[1] "%d Nachrichten wurden gelöscht."
 
    api: plural("nachricht multi delete", $c);
 */
function plural($msgid, $total) {
    return sprintf(
        ngettext($msgid, "plural $msgid", $total),
        $total
    );
}

function text_for($muster, $vars = [], $brackets = '{}') {
    $repl = array();
    foreach ($vars as $k => $v) {
        if (preg_match("/^datum_/", $k)) {
            $v = hum_date($v);
        }
        if (preg_match("/^euro_/", $k)) {
            $v = geld($v, 0);
        }
        $repl[$brackets[0] . strtolower($k) . $brackets[1]] = $v;
    }
    $txt = $muster;
    $txt = str_replace(array_keys($repl), $repl, $txt);
    $txt = replace_links($txt);
    return $txt;
}

function replace_links($t) {
    return preg_replace_callback("/\[([^\]]*?)\]\(link: ([.\/#\w]+)\)/", function ($m) {

        if (preg_match("/^x_(.*?)$/", $m[2], $mat)) {
            # vordefinierter externer link?

            $url = url("redir/to", $mat[1]);
            $class = "";
            $target = 'target="_blank"';
        } elseif (preg_match("!doc/(.*?)\.pdf$!", $m[2], $mat)) {
            # generische pdfs?
            $url = url("download/doc", $mat[1]);
            $class = "";
            $target = '';
        } elseif (preg_match("/(.*?)\.pdf$/", $m[2], $mat)) {
            # pdfs?
            $url = url("download/{$mat[1]}");
            $class = "";
            $target = '';
        } elseif (preg_match("!/!", $m[2])) {
            # controller/action links?
            $url = url($m[2]);
            $class = "";
            $target = '';
        } else {
            # info links in neuem fenster
            list($action, $hash) = explode('#', $m[2], 2);
            if ($hash) $url = url("info/{$action}", array('#' => $hash));
            else $url = url("info/{$m[2]}");
            #$class = 'window-500x400';
            $target = '';
        }
        return sprintf('<a href="%s" class="%s" %s>%s</a>', $url, $class, $target, $m[1]);
    }, $t);
}
