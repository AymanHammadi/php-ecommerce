<?php
function getTitle()
{
    global $pageTitle;
    if (isset($pageTitle)) {
        return htmlspecialchars($pageTitle);
    } else {
        return 'PHP E-commerce';
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}
