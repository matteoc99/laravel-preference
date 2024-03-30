<?php

namespace Matteoc99\LaravelPreference\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Exceptions\ConfigException;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PreferenceController extends Controller
{

    private PreferenceableModel|null        $scope = null;
    private PreferenceGroup|null $group = null;


    public function index(Request $request, int $scope_id)
    {
        $this->init($request);

    }

    public function get(Request $request, int $scope_id, string $preference)
    {
        $this->init($request);

    }

    public function update(Request $request, int $scope_id, string $preference)
    {
        $this->init($request);

    }

    public function destroy(Request $request, int $scope_id, string $preference)
    {
        $this->init($request);

    }


    private function init(Request $request)
    {
        try {
            list('scope' => $scope, 'group' => $group) = $this->extractScopeAndGroup($request->route()->getName());
            $scopeClass = ConfigHelper::getScope($scope);
            $groupClass = ConfigHelper::getGroup($group);

     //todo      $this->scope = $this->validateAndInstantiate($scopeClass, PreferenceableModel::class);
     //todo      $this->group = $this->validateAndInstantiate($groupClass, PreferenceGroup::class);

        } catch (ConfigException $e) {
            // Handle invalid configuration, log error, and respond appropriately (e.g., HTTP 400 error)
            logger()->error("Configuration error: " . $e->getMessage());
            throw new BadRequestHttpException("Invalid configuration");
        }
    }

    /*
     * @throws ConfigException If validation fails
     */
    private function validateAndInstantiate(string $className, string $interfaceName): object
    {

        if (!class_exists($className)) {
            throw new ConfigException("Class '$className' not found.");
        }

        if (!in_array($interfaceName, class_implements($className))) {
            throw new ConfigException("Class '$className' does not implement '$interfaceName'.");
        }

        return new $className();
    }

    private function extractScopeAndGroup($routeName)
    {
        $prefix = ConfigHelper::getRoutePrefix();

        $routeSegments = explode('.', Str::replaceStart($prefix, "", $routeName));

        return [
            'scope' => $routeSegments[0],
            'group' => $routeSegments[1]
        ];
    }
}