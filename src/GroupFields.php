<?php

namespace Mavericks\GroupFields;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Illuminate\Support\Facades\Request as RequestFacade;

class GroupFields extends Field
{
    /**
     * Constants for placeholders.
     */
    const INDEX = '{{index}}';
    const ATTRIBUTE = '__attribute';
    const ID = '__id';

    /*
     * Store the fields
     */
    protected $fields = [];

    /**
     * From resource uriKey.
     *
     * @var string
     */
    public $viaResource;

    /*
     *  Stores singular label
     */
    public $singularLabel;

    /*
     *  Stores plural label
     */
    public $pluralLabel;

    /*
     * For resource relationship
     */
    public $viaRelationship;
    /**
     * Prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct(string $name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);
        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);
        $this->resourceClass = $resource;
        $this->pluralLabel = Str::plural($this->name);
        $this->viaResource = app(NovaRequest::class)->route('resource');
        $this->viaRelationship = $this->attribute;
        $this->returnContext = $this;
        $this->setRequest();
    }

    /**
     * Set the current request instance.
     *
     * @param Request $request
     * @return self
     */
    protected function setRequest(Request $request = null)
    {
        $this->request = $request ?? NovaRequest::createFrom(RequestFacade::instance());

        return $this;
    }

    /**
     * The instance of the related resource.
     *
     * @var string
     */
    public $resourceInstance;

    public function fields($fields)
    {
        $this->fields = collect($fields);

        return $this;
    }

    /**
     * Set heading.
     *
     * @return self
     */
    protected function getHeading()
    {
        return trim($this->prefix . ($this->heading ?? $this->defaultHeading()));
    }

    /**
     * Default heading.
     *
     * @return string
     */
    protected function defaultHeading()
    {
        return Str::singular($this->name);
    }

    /**
     * Set a maximum number of children.
     *
     * @param int $max
     *
     * @return self
     */
    public function max(int $max)
    {
        return $this->withMeta([
            'max' => $max,
        ]);
    }

    /**
     * Set a minimum number of children.
     *
     * @param int $min
     *
     * @return self
     */
    public function min(int $min)
    {
        return $this->withMeta([
            'min' => $min,
        ]);
    }

    /**
     * Set the related schema.
     *
     * @return self
     */
    public function setSchema()
    {
        return $this->withMeta([
            'schema' => [
                'fields' => $this->getFields(),
                'heading' => $this->getHeading(),
                'attribute' => $this->attribute . '[' . self::INDEX . ']',
            ],
        ]);
    }

    /**
     * Get all the grouped fields
     *
     * @return array
     */
    private function groupedFields()
    {
        return $this->fields;
    }

    /**
     * Get the related fields.
     *
     * @param string $filterKey
     * @param Model|null $model
     * @param $index
     * @return self
     */
    public function getFields(Model $model = null, $index = self::INDEX)
    {
        return $this->groupedFields()->map(function ($field) use ($index, $model)
        {
            $field->withMeta([
                'original_attribute' => $field->attribute,
                'attribute' => ($this->meta['attribute'] ?? $this->attribute) . ('[' . $index . ']') . '[' . $field->attribute . ']',
            ]);

            if ($field->component === $this->component) {
                $field->prefix = ($this->prefix ? $this->prefix : '') . (is_int($index) ? $index + 1 : $index) . $field->separator;
            }

            $field->resolve($model);

            return $field;
        })->values();
    }

    /**
     * Set the related children.
     *
     * @param $resource
     * @return self
     */
    public function setChildren()
    {
        return $this->withMeta([
            'children' => []
        ]);
    }

    /**
     * Set the viaResource information as meta.
     *
     * @param Model $resource
     *
     * @return self
     */
    protected function setViaResourceInformation()
    {
        return $this->withMeta([
            'heading' => $this->getHeading(),
            'singularLabel' => $this->defaultHeading(),
            'pluralLabel' => $this->pluralLabel,
            'viaResource' => $this->viaResource,
            'viaRelationship' => $this->viaRelationship,
            'INDEX' => self::INDEX,
            'ATTRIBUTE' => self::ATTRIBUTE,
            'ID' => self::ID,
        ]);
    }

    /**
     * Resolve additional param
     *
     * @param mixed $resource
     * @param null $attribute
     */
    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute);

        $this->setViaResourceInformation()->setSchema()->setChildren();
    }

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'group-fields';
}
