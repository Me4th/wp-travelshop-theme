<?php

class TSWPCategoryHeader extends FLBuilderModule {

    /**
     * @method __construct
     */
    public function __construct() {
        parent::__construct(array(
            'name'            => __( 'Category Header', 'fl-builder' ),
            'description'     => __( 'customizable category header', 'fl-builder' ),
            'category'        => __( 'Content Modules', 'fl-builder' ),
            'group'        => __( 'Travelshop', 'fl-builder' ),
            'editor_export'   => false,
            'partial_refresh' => true,
            'icon'            => 'slides.svg',
            'dir'           => BB_MODULE_TS_DIR . 'modules/category-header/',
            'url'           => BB_MODULE_TS_URL . 'modules/category-header/',
        ));
    }

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('TSWPCategoryHeader', array(

    'common' => array(
        'title'    => __( 'Common', 'fl-builder' ),
        'sections' => array(
            'media' => array(
                'title' => __('Hintergrund', 'fl-builder' ),
                'fields' => array(
                    'media_type' => array(
                        'type' => 'select',
                        'label' => __('Media-Type', 'fl-builder'),
                        'default' => 'image',
                        'options' => array(
                            'image' => 'Image',
                            'video' => 'Video'
                        ),
                        'toggle' => array(
                            'image' => array(
                                'fields' => array('image', 'image_alt_text')
                            ),
                            'video' => array(
                                'fields' => array('video')
                            )
                        )
                    ),

                    'video'    => array(
                        'type'          => 'video',
                        'label'         => __('Slide-Video', 'fl-builder'),
                    ),

                    'image'    => array(
                        'type'          => 'photo',
                        'label'         => __('Slide-Image', 'fl-builder'),
                    ),

                    'image_alt_text'     => array(
                        'type'    => 'text',
                        'label'   => __( 'Image alternative text', 'fl-builder' ),
                        'default' => '',
                    ),


                    'background_overlay_type' => array(
                        'type' => 'select',
                        'label' => __('Background overlay type', 'fl-builder'),
                        'options' => array(
                            'none' => 'None',
                            'color' => 'Color',
                            'gradient' => 'Gradient'
                        ),
                        'default' => 'none',
                        'toggle' => array(
                            'none' => array(
                                'fields' => array()
                            ),
                            'color' => array(
                                'fields' => array('background_overlay_color')
                            ),
                            'gradient' => array(
                                'fields' => array('background_overlay_gradient')
                            )
                        )
                    ),
                    'background_overlay_color' => array(
                        'type' => 'color',
                        'label' => __('Background overlay color', 'fl-builder'),
                        'show_reset' => true,
                        'show_alpha' => true
                    ),
                    'background_overlay_gradient' => array(
                        'type' => 'gradient',
                        'label' => __('Background overlay gradient', 'fl-builder'),
                        'show_reset' => true,
                        'show_alpha' => true,
                        'preview' => array(
                            'type'     => 'css',
                            'selector' => '.category-header-overlay--gradient',
                            'property' => 'background-image',
                        ),
                    ),

                    'background_height' => array(
                        'type' => 'unit',
                        'label' => __('Background height', 'fl-builder'),
                        'description' => 'px',
                        'placeholder' => 100,
                        'default' => 100,
                        'responsive'  => true,
                    )

                )
            ),
            'content'    => array(
                'title'  => __( 'Content', 'fl-builder' ),
                'fields' => array(

                    'headline'     => array(
                        'type'    => 'text',
                        'label'   => __( 'Headline', 'fl-builder' ),
                        'default' => 'Headline',
                    ),

                    'headline_type' => array(
                        'type' => 'select',
                        'label' => __('Headline type', 'fl-builder'),
                        'options' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ),
                        'default' => 'h1'
                    ),

                    'subline'     => array(
                        'type'    => 'text',
                        'label'   => __( 'Subline', 'fl-builder' ),
                        'default' => 'Subline',
                    ),

                    'subline_type' => array(
                        'type' => 'select',
                        'label' => __('Subline type', 'fl-builder'),
                        'options' => array(
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ),
                        'default' => 'h2'
                    ),

                    'text'     => array(
                        'type'    => 'editor',
                        'media_buttons' => false,
                        'wpautop'       => false,
                        'label'   => __( 'Text', 'fl-builder' ),
                        'default' => 'Some more informations'
                    ),
                ),
            ),

            'position' => array(
              'title' => __('Position', 'fl-builder'),
              'fields' => array(
                  'content_alignment_vertical' => array(
                      'type' => 'select',
                      'label' => __('Vertical content alignment', 'fl-builder'),
                      'options' => array(
                          'top' => 'Top',
                          'middle' => 'Middle',
                          'bottom' => 'Bottom',
                          'underneath' => 'unter Inhalt'
                      ),
                      'responsive' => true,
                      'default' => 'middle'
                  ),
                  'content_alignment_horizontal' => array(
                      'type' => 'select',
                      'label' => __('Horizontal content alignment', 'fl-builder'),
                      'options' => array(
                          'left' => 'Left',
                          'center' => 'Center',
                          'right' => 'Right'
                      ),
                      'responsive' => true,
                      'default' => 'center'
                  ),
              )
            ),

            'style' => array(
                'title' => __('Style', 'fl-builder'),
                'fields' => array(
                    'content_box_text_align' => array(
                        'type' => 'select',
                        'label' => __('Content box inner text align', 'fl-builder'),
                        'options' => array(
                            'text-left' => 'left',
                            'text-center' => 'center',
                            'text-right' => 'right'
                        ),
                        'default' => 'text-left'
                    ),
                    'content_box_type' => array(
                        'type' => 'select',
                        'label' => __('Content box type', 'fl-builder'),
                        'options' => array(
                            'transparent' => 'Transparent',
                            'boxed' => 'Boxed',
                            'docked' => 'Docked'
                        ),
                        'default' => 'transparent',
                        'toggle' => array(
                            'boxed' => array(
                                'fields' => array('content_box_background')
                            )
                        )
                    ),
                    'content_box_background' => array(
                        'type' => 'color',
                        'label'         => __( 'Content box background', 'fl-builder' ),
                        'default'       => 'FFFFFF',
                        'show_reset'    => true,
                        'show_alpha'    => true
                    ),
                    'headline_color' => array(
                        'type'       => 'color',
                        'label'      => __('Headline color', 'fl-builder'),
                        'default'       => 'FFFFFF',
                        'show_reset' => true,
                        'show_alpha' => true
                    ),
                    'subline_color' => array(
                        'type'       => 'color',
                        'label'      => __('Subline color', 'fl-builder'),
                        'default'       => 'FFFFFF',
                        'show_reset' => true,
                        'show_alpha' => true
                    ),
                    'text_color' => array(
                        'type'       => 'color',
                        'label'      => __('Text color', 'fl-builder'),
                        'default'       => 'FFFFFF',
                        'show_reset' => true,
                        'show_alpha' => true
                    ),

                )
            )

        ),
    ),


));
