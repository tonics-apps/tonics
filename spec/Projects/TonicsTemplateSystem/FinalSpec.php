<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Spec;

use Devsrealm\TonicsTemplateSystem\TonicsView;

describe("FinalOuput", function () {

    beforeEach(function () {
        $this->view->reset();
    });

    it("should match sample one", function () {
        /*** @var TonicsView $view */
        $view = $this->view;
        $html = <<<EOD
<html>
</html>
EOD;
        $arrayTemplates = [
            'main' => $html,
        ];
        $loader = $this->arrayLoader->setTemplates($arrayTemplates);
        $view->setTemplateLoader($loader);
        expect($view->render('main', TonicsView::RENDER_CONCATENATE))->toBe("<html>\n</html>");
    });

    it("should match sample two", function () {
        /*** @var TonicsView $view */
        $view = $this->view;
        $html = <<<EOD
<html>
    [[[
        [[block('content')
            [[use('invalid')
                [[nested()
                ]]
             ]]
        ]]
    ]]]
</html>
EOD;
        $arrayTemplates = [
            'main' => $html,
        ];
        $loader = $this->arrayLoader->setTemplates($arrayTemplates);
        $view->setTemplateLoader($loader);
        expect($view->render('main', TonicsView::RENDER_CONCATENATE))->toBe("<html>\n    \n        [[block('content')\n            [[use('invalid')\n                [[nested()\n                ]]\n             ]]\n        ]]\n    \n</html>");
    });

    it("should match sample three", function () {
        /*** @var TonicsView $view */
        $view = $this->view;
        $html = <<<EOD
[[import("module1")]]
<html>
    <head>
        <title>App Name - [[v('title')]]</title>
    </head>
    <body>
        <div class="container">
            [[use("content")]] App Name - [[_v('vary.in.in')]]
            [[use("content")]] App Name - [[v('new')]]
            [[use("content")]] App Name - [[v('new')]]
        </div>
    </body>
</html>
EOD;
        $module1 = <<<EOD
[[b('content')
    <p>This is my body content.</p>
]]
EOD;
        $arrayTemplates = [
            'main'    => $html,
            'module1' => $module1,
        ];
        $view->setVariableData([
            'title'  => 'Fancy Value 55344343',
            'title2' => 'Fancy Value 2',
            'title3' => 'Fancy Value 3',
            'new'    => 'This is the new',
            'vary'   => [
                'in' => [
                    'in' => '<script type="text/javascript" src="js/test/fm.js"></script>',
                ],
            ],
        ]);
        $loader = $this->arrayLoader->setTemplates($arrayTemplates);
        $view->setTemplateLoader($loader);
        expect($view->render('main', TonicsView::RENDER_CONCATENATE))->toBe("\n<html>\n    <head>\n        <title>App Name - Fancy Value 55344343</title>\n    </head>\n    <body>\n        <div class=\"container\">\n            \n    &lt;p&gt;This is my body content.&lt;/p&gt;\n App Name - <script type=\"text/javascript\" src=\"js/test/fm.js\"></script>\n            \n    &lt;p&gt;This is my body content.&lt;/p&gt;\n App Name - This is the new\n            \n    &lt;p&gt;This is my body content.&lt;/p&gt;\n App Name - This is the new\n        </div>\n    </body>\n</html>");
    });

});