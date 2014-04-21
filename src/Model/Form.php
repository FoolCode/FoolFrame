<?php

namespace Foolz\Foolframe\Model;


use Symfony\Component\HttpFoundation\Request;

class Form
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function __call($name, $args)
    {
        return call_user_func_array([$this, 'generic'], ['type' => $name] + $args);
    }

    public function open($args = false, $hidden = [])
    {
        if ($args === false) {
            $args = $this->request->getUri();
        }

        if (!is_array($args)) {
            $args = '/' . ltrim($args, '/');
            $args = ['action' => $this->request->getUriForPath($args)];
        }

        if (!isset($args['method'])) {
            $args['method'] = 'POST';
        }

        $s = '<form'.$this->expandArgs($args).'>';

        foreach($hidden as $name => $value) {
            $s .= '<input type="hidden" name="'.htmlentities($name).'" value="'.htmlentities($value).'">';
        }

        return $s;
    }

    public function close()
    {
        return '</form>';
    }

    public function generic($type, $args)
    {
        if ($type) {
            $args['type'] = $type;
        }

        return '<input'.$this->expandArgs($args).'>';
    }

    public function input($args)
    {
        if (!is_array($args)) {
            $args = ['name' => $args];
        }

        return $this->generic('text', $args);
    }

    public function hidden($field, $value, $args = [])
    {
        $s = '<input type="hidden"';

        $args['name'] = $field;

        if ($value) {
            $args['value'] = $value;
        }

        $s .= $this->expandArgs($args).'>';

        return $s;
    }

    public function textarea($args)
    {
        $value = '';
        if (isset($args['value'])) {
            $value = $args['value'];
            unset($args['value']);
        }

        return '<textarea'.$this->expandArgs($args).'>'.htmlspecialchars($value).'</textarea>';
    }

    public function select($field, $default, $values, $args = [])
    {
        $s = '<select';

        $args['name'] = $field;

        $s .= $this->expandArgs($args).'>';

        foreach ($values  as $key => $value) {
            $s .= '<option value="'.htmlentities($key).'"'.($default === $key ? ' selected' : '').'>'.htmlspecialchars($value).'</option>';
        }

        $s .= '</select>';

        return $s;
    }

    public function radio($field, $value = null, $checked = false, $args = [], $type = 'radio')
    {
        $s = '<input type="'.$type.'"';

        $args['name'] = $field;

        if (is_array($field)) {
            $args = $field;
        } else {
            if ($value) {
                $args['value'] = $value;
            }

            if ($checked) {
                $args['checked'] = 'checked';
            }
        }

        $s .= $this->expandArgs($args).'>';

        return $s;
    }

    public function checkbox($field, $value = null, $checked = false, $args = [])
    {
        return $this->radio($field, $value, $checked, $args, 'checkbox');
    }

    public function label($text, $for)
    {
        return '<label for="'.htmlentities($for).'">'.htmlspecialchars($text).'</label>';
    }

    protected function expandArgs($args)
    {
        $s = '';
        foreach($args as $key => $value) {
            $s .= ' '.htmlentities($key). '="'.htmlentities($value).'"';
        }

        return $s;
    }
}
