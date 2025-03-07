<?php

namespace Elgg\IndieWeb\IndieAuth\Actions;

class DeauthorizeAction
{
    public function __invoke(\Elgg\Request $request)
    {

        unset($_SESSION['indieauth']);

        return elgg_ok_response('', elgg_echo('indieweb:indieauth:authorize:cancel'), $request->getParam('redirect_uri'));
    }
}
