<?php
/**
 * @package     EasyDocs
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://ait-themes.club
 */

namespace EasyDocLabs\EasyDoc;

use EasyDocLabs\Library;

class TemplateHelperSelect extends Library\TemplateHelperSelect
{
    /**
     * Generates an HTML boolean radio list
     *
     * @param   array|ObjectConfig     $config An optional array with configuration options
     * @return  string  Html
     */
    public function status($config = [])
    {
        $translator = $this->getObject('translator');

        $config = new library\ObjectConfigJson($config);
        $config->append([
            'name'   	=> '',
            'options' => [],
            'attribs'	=> [],
            'true'		=> $translator->translate('Yes'),
            'false'		=> $translator->translate('No'),
            'selected'	=> null,
            'translate'	=> true
        ]);

        $name    = $config->name;
        $options = [];

        $html  = [];

        $html[] = '<div class="k-optionlist k-optionlist--boolean">';
        $html[] = '<div class="k-optionlist__content">';

        $options[] = $this->option([
            'label' => $translator->translate('Draft'),
            'value' => 0,
            'id' => ModelEntityDocument::STATE_DRAFTED,
            'attribs' => $config->attribs
        ]);
        $options[] = $this->option([
            'label' => $translator->translate('Scheduled'),
            'value' => 2,
            'id' => ModelEntityDocument::STATE_SCHEDULED,
            'attribs' => $config->attribs
        ]);
        $options[] = $this->option([
            'label' => $translator->translate('Published') ,
            'value' => 1,
            'id' => ModelEntityDocument::STATE_PUBLISHED,
            'attribs' => $config->attribs
        ]);

        //Add the options to the config object
        $config->options = $options;

        foreach($config->options as $option)
        {
            $value = $option->value;
            $label = $config->translate ? $translator->translate( $option->label ) : $option->label;

            $extra = ($value == $config->selected ? 'checked="checked"' : '');

            if(isset($option->disabled) && $option->disabled) {
                $extra .= 'disabled="disabled"';
            }

            $attribs = isset($option->attribs) ? $this->buildAttributes($option->attribs) : '';

            $html[] = '<input type="radio" name="'.$name.'" id="'.$name.$option->id.'" value="'.$value.'" '.$extra.' '.$attribs.' />';
            $html[] = '<label for="'.$name.$option->id.'"><span>';
            $html[] = $label;
            $html[] = '</span></label>';
        }




        $html[] = '<div class="k-optionlist__focus"></div>';
        $html[] = '</div>';
        $html[] = '</div>';

        return implode(PHP_EOL, $html);
    }
}
