<?php

declare(strict_types=1);

namespace Bonu\ElasticsearchBuilder\Sort;

enum SortDirectionEnum: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
