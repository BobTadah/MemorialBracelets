<?php
return array(
    'system' =>
        array(
            'default' =>
                array(
                    'dev' =>
                        array(
                            'restrict' =>
                                array(),
                            'debug' =>
                                array(
                                    'template_hints_storefront' => '0',
                                    'template_hints_admin' => '0',
                                    'template_hints_blocks' => '0',
                                ),
                            'template' =>
                                array(
                                    'allow_symlink' => '0',
                                    'minify_html' => '1',
                                ),
                            'translate_inline' =>
                                array(
                                    'active' => '0',
                                    'active_admin' => '0',
                                ),
                            'js' =>
                                array(
                                    'enable_js_bundling' => '0',
                                    'merge_files' => '1',
                                    'minify_files' => '1',
                                ),
                            'css' =>
                                array(
                                    'merge_css_files' => '1',
                                    'minify_files' => '1',
                                ),
                            'static' =>
                                array(
                                    'sign' => '1',
                                ),
                        ),
                ),
        )
);
