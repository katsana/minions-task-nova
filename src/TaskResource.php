<?php

namespace Minions\Task\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class TaskResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'Minions\Task\Task';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            $this->creatorRelatedField($request),

            Text::make('Project'),
            Text::make('Method'),

            Status::make('Status')
                    ->loadingWhen(['created', 'running'])
                    ->failedWhen(['cancelled', 'failed']),

            Code::make('Payload')
                ->json()
                ->options(['lineNumbers' => false])
                ->onlyOnDetail(),

            Code::make('Exception')
                ->json()
                ->options(['lineNumbers' => false])
                ->onlyOnDetail(),

            DateTime::make('Created At')->format('D MMM YYYY, LTS')->sortable(),
            DateTime::make('Updated At')->format('D MMM YYYY, LTS')->onlyOnDetail(),
        ];
    }

    /**
     * Creator related field.
     *
     * @return \Laravel\Nova\Fields\MorphTo
     */
    protected function creatorRelatedField(Request $request)
    {
        return MorphTo::make('Creator');
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Filters\TaskStatusFilter(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        if ($request->action) {
            switch($request->action) {
                case 'minions-retry-task':
                    return $this->authorizedToRunRetryTaskAction($request);
                case 'minions-cancel-task':
                    return $this->authorizedToRunCancelTaskAction($request);
                default:
                    return $this->authorizedToRunAnyAction($request)
            }
        }

        return false;
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }


    /**
     * Determine if current user can run any action.
     *
     * @return bool
     */
    protected function authorizedToRunAnyAction(Request $request)
    {
        return false;
    }

    /**
     * Determine if current user can run "cancel task" action.
     *
     * @return bool
     */
    protected function authorizedToRunCancelTaskAction(Request $request)
    {
        return false;
    }

    /**
     * Determine if current user can run "retry task" action.
     *
     * @return bool
     */
    protected function authorizedToRunRetryTaskAction(Request $request)
    {
        return false;
    }
}
