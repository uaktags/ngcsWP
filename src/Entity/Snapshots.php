<?php

/*
 * This file is part of the NGCSv1 library.
 *
 * (c) Tim Garrity <timgarrity89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NGCSv1\Entity;

/**
 * @author Tim Garrity <timgarrity89@gmail.com>
 */
class Snapshots extends AbstractEntity
{
    /**
     * @var int
     */
    public $id;
    public $creation_date;
    public $deletion_date;

    /**
     * @param \stdClass|array $parameters
     */
    public function build($parameters)
    {
        foreach ($parameters as $property => $value) {
            switch ($property) {
                case 'id':
                    $this->id = $value;
                    break;
                case 'creation_date':
                    $this->creation_date = $value;
                    break;
                case 'deletion_date':
                    $this->deletion_date = $value;
                    break;
                default:
                    $this->{\NGCSv1\convert_to_camel_case($property)} = $value;
                    break;
            }
        }
    }
}