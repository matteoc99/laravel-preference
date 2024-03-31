<?php

namespace Matteoc99\LaravelPreference\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Exceptions\ConfigException;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Matteoc99\LaravelPreference\Http\Requests\PreferenceUpdateRequest;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreferenceController extends Controller
{

    private PreferenceableModel    $scope;
    private PreferenceGroup|string $group;


    public function __construct(Request $request)
    {
        $this->init($request);
    }


    public function index(Request $request): JsonResponse
    {
        return $this->prepareResponse(
            $this->scope->getPreferences($this->group)
        );
    }

    public function get(Request $request): JsonResponse
    {
        return $this->prepareResponse(
            $this->scope->getPreference($this->group)
        );
    }

    public function update(PreferenceUpdateRequest $request): JsonResponse
    {

        $value = $request->validated('value');
        $this->scope->setPreference($this->group, $value);

        return $this->prepareResponse(
            $this->scope->getPreference($this->group)
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->scope->removePreference($this->group);

        return $this->prepareResponse(
            $this->scope->getPreference($this->group)
        );
    }


    private function init(Request $request): void
    {
        try {
            list('scope' => $scope, 'group' => $group) = $this->extractScopeAndGroup($request->route()->getName());

            $params = $request->route()->originalParameters();

            $id         = $params['scope_id'] ?? null;
            $preference = $params['preference'] ?? null;

            if (empty($id)) {
                throw new ConfigException("Scope ID is required.");
            }

            $scopeClass = ConfigHelper::getScope($scope);
            $groupClass = ConfigHelper::getGroup($group);


            $this->validate($scopeClass, PreferenceableModel::class);
            $this->validate($groupClass, PreferenceGroup::class);


            $this->group = $groupClass;
            $this->scope = $scopeClass::findOrFail($id);

            if (!empty($preference)) {
                try {
                    $this->group = $groupClass::from($preference);
                } catch (\ValueError $e) {
                    throw new PreferenceNotFoundException($e->getMessage());
                }
            }

        } catch (ConfigException $e) {
            throw new BadRequestHttpException("Invalid configuration", $e);
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundHttpException('Scope not found', $exception);
        } catch (PreferenceNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        } catch (\Throwable $exception) {
            throw new HttpException(500, 'Internal Server Error', $exception);
        }
    }

    /*
     * @throws ConfigException If validation fails
     */
    private function validate(string $className, string $interfaceName): void
    {

        if (!class_exists($className)) {
            throw new ConfigException("Class '$className' not found.");
        }

        if (!in_array($interfaceName, class_implements($className))) {
            throw new ConfigException("Class '$className' does not implement '$interfaceName'.");
        }
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

    private function prepareResponse(mixed $pref): JsonResponse
    {
        if (!empty($pref) && is_object($pref) && method_exists($pref, 'toArray')) {
            $pref = $pref->toArray();
        }

        if (!is_array($pref)) {
            $pref = [
                'value' => $pref,
            ];
        }
        return response()->json($pref);
    }
}