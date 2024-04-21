<?php

namespace Matteoc99\LaravelPreference\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
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
use Throwable;

class PreferenceController extends Controller
{

    private PreferenceableModel    $scope;
    private PreferenceGroup|string $group;


    public function __construct(Request $request)
    {
        try {
            $this->init($request);
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }


    public function index(Request $request): JsonResponse
    {
        try {
            return response()->json($this->scope->getPreferences($this->group));
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function get(Request $request): JsonResponse
    {
        try {
            return $this->prepareResponse();
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    public function update(PreferenceUpdateRequest $request): JsonResponse
    {

        try {
            $value = $request->validated('value');
            $this->clean($value);

            $this->scope->setPreference($this->group, $value);

            return $this->prepareResponse();
        } catch (Throwable $exception) {
            return $this->handleException($exception);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            $this->scope->removePreference($this->group);

            return $this->prepareResponse();
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }


    private function init(Request $request): void
    {
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
    }

    /**
     * @throws ConfigException
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

    private function extractScopeAndGroup($routeName): array
    {
        $prefix = ConfigHelper::getRoutePrefix();

        $routeSegments = explode('.', Str::replaceStart($prefix, "", $routeName));

        return [
            'scope' => $routeSegments[0],
            'group' => $routeSegments[1]
        ];
    }

    /**
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    private function prepareResponse(): JsonResponse
    {
        return response()->json($this->scope->getPreferenceDto($this->group));
    }

    /**
     * @throws Throwable
     */
    private function handleException(Throwable|\Exception $exception)
    {
        match ($exception::class) {
            ConfigException::class => throw new BadRequestHttpException("Invalid configuration", $exception),
            ModelNotFoundException::class, PreferenceNotFoundException::class => throw new NotFoundHttpException($exception->getMessage(), $exception),
            AuthorizationException::class => throw new HttpException(403, $exception->getMessage(), $exception),
            default => throw $exception, // Now this throw handles any other type of exception
        };
    }

    private function clean(mixed &$value): void
    {
        if (ConfigHelper::isXssCleanEnabled()) {
            if (is_string($value)) {
                $value = \GrahamCampbell\SecurityCore\Security::create()->clean($value);
            } elseif (is_iterable($value)) {
                foreach ($value as &$item) {
                    if (is_string($item)) {

                        $item = \GrahamCampbell\SecurityCore\Security::create()->clean($item);
                    }
                }
                unset($item);
            }
        }
    }
}