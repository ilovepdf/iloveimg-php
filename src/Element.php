<?php

namespace Iloveimg;

class Element
{

    /**
     * @var string
     */
    public $type = 'text';

    /**
     * @var string
     */
    public $mode = 'text';

    /**
     * @var string
     */
    public $text = null;

    /**
     * @var string
     */
    public $image = null;

    /**
     * @var integer
     */
    public $vertical_adjustment = 0;

    /**
     * @var integer
     */
    public $horizontal_adjustment = 0;

    /**
     * @var integer
     */
    public $vertical_adjustment_percent = 0;

    /**
     * @var integer
     */
    public $horizontal_adjustment_percent = 0;


    /**
     * @var int
     */
    public $width_percent = 100;

    /**
     * @var int
     */
    public $height_percent = 100;

    /**
     * @var integer
     */
    public $x_pos_percent = 0;

    /**
     * @var integer
     */
    public $y_pos_percent = 0;

    /**
     * @var integer
     */
    public $rotation = 0;

    /**
     * @var integer
     */
    public $transparency = 100;

    /**
     * @var integer
     */
    public $opacity = 100;

    /**
     * @var bool
     */
    public $mosaic = false;

    /**
     * @var string
     */
    public $font_family = 'Arial';

    private $fontFamilyValues = ['Arial', 'Arial Unicode MS', 'Verdana', 'Courier', 'Times New Roman', 'Comic Sans MS', 'WenQuanYi Zen Hei', 'Lohit Marathi'];

    /**
     * @var string
     */
    public $font_style = 'Regular';

    /**
     * @var string
     */
    public $font_weight = null;

    /**
     * @var string
     */
    public $font_color = '#000000';

    /**
     * @var
     */
    public $color_shadow;

    /**
     * @var
     */
    public $font_size = 14;

    /**
     * @var
     */
    public $image_resize = 1;

    /**
     * @var
     */
    public $zoom = 1;

    /**
     * @var
     */
    public $gravity = 'Center';
    private $gravityValues = ['North', 'NorthEast', 'NorthWest', 'Center', 'CenterEast', 'CenterWest', 'East', 'West', 'South', 'SouthEast', 'SouthWest'];


    /**
     * @var int
     */
    public $border;

    /**
     * @var string
     */
    public $layer;


    /**
     * @var bool
     */
    public $bold = false;

    /**
     * string
     * @var
     */
    public $server_filename;

    public function __construct($values = null)
    {
        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if (property_exists(self::class, $name)) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @param string $mode
     * @return Element
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $text
     * @return Element
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param string $image
     * @return Element
     */
    public function setImage($image)
    {
        if (get_class($image) === 'Iloveimg\File') {
            $this->image = $image->getServerFilename();
        }
        else{
            $this->image = $image;
        }
        $this->setType('image');
        return $this;
    }

    /**
     * @param int $rotation
     */
    public function setRotation($rotation)
    {
        $this->rotation = $rotation;
        return $this;
    }

    /**
     * @param string $font_family
     */
    public function setFontFamily($font_family)
    {
        $this->checkValues($font_family, $this->fontFamilyValues);

        $this->font_family = $font_family;
        return $this;
    }

    /**
     * @param string $font_style
     */
    public function setFontStyle($font_style)
    {
        $this->font_style = $font_style;
        return $this;
    }

    /**
     * @param string $font_weight
     */
    public function setFontWeight($font_weight)
    {
        $this->font_weight = $font_weight;
        return $this;
    }

    /**
     * @param int $font_size
     */
    public function setFontSize($font_size)
    {
        $this->font_size = $font_size;
        return $this;
    }

    /**
     * @param string $font_color
     */
    public function setFontColor($font_color)
    {
        $this->font_color = $font_color;
        return $this;
    }

    /**
     * @param string $font_color
     */
    public function setColorShadow($color_shadow)
    {
        $this->color_shadow = $color_shadow;
        return $this;
    }

    /**
     * @param int $transparency
     */
    public function setTransparency($transparency)
    {
        $this->transparency = $transparency;
        return $this;
    }


    /**
     * @param string $vertical_position
     */
    public function setVerticalPosition($vertical_position)
    {
        $this->checkValues($vertical_position, $this->verticalPositionValues);

        $this->vertical_position = $vertical_position;
        return $this;
    }

    /**
     * @param string $horizontal_position
     */
    public function setHorizontalPosition($horizontal_position)
    {
        $this->checkValues($horizontal_position, $this->horizontalPositionValues);

        $this->horizontal_position = $horizontal_position;
        return $this;
    }

    /**
     * @param int $vertical_position_adjustment
     */
    public function setVerticalPositionAdjustment($vertical_position_adjustment)
    {
        $this->vertical_position_adjustment = $vertical_position_adjustment;
        return $this;
    }

    /**
     * @param int $horizontal_position_adjustment
     */
    public function setHorizontalAdjustmentPercent($horizontal_adjustment_percent): Element
    {
        $this->horizontal_adjustment_percent = $horizontal_adjustment_percent;
        return $this;
    }

    /**
     * @param $gravity
     * @return $this
     */
    public function setGravity($gravity): Element
    {
        $this->checkValues($gravity, $this->gravityValues);

        $this->gravity = $gravity;
        return $this;
    }

    /**
     * @param int $width_percent
     * @return $this
     */
    public function setWidthPercent(int $width_percent): Element
    {
        $this->width_percent = $width_percent;
        return $this;
    }

    /**
     * @param mixed $value
     * @param array $allowed
     *
     * @return ImageTask
     */
    public function checkValues($value, $allowedValues)
    {
        if (!in_array($value, $allowedValues)) {
            throw new \InvalidArgumentException('Invalid value "' . $value . '". Must be one of: ' . implode(',', $allowedValues));
        }
    }

    /**
     * @param bool $mosaic
     * @return Element
     */
    public function setMosaic(bool $mosaic): Element
    {
        $this->mosaic = $mosaic;
        return $this;
    }

}