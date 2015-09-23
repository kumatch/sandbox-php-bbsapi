<?php
namespace Kumatch\BBSAPI\Application\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Kumatch\BBSAPI\Application\Request;
use Kumatch\BBSAPI\UseCase\ThreadManagement;
use Kumatch\BBSAPI\UseCase\TagRegistration;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Value\Tags;
use Kumatch\BBSAPI\Spec\ThreadSpec;
use Kumatch\BBSAPI\Spec\TagsSpec;

class BBSAPIThreadServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.thread.management"] = function (Application $app) {
            return new ThreadManagement($app["entity_manager"]);
        };
        $app["bbsapi.tag.registration"] = function (Application $app) {
            return new TagRegistration($app["entity_manager"]);
        };

        $app["bbsapi.spec.thread_spec"] = function () {
            return new ThreadSpec();
        };
        $app["bbsapi.spec.tags_spec"] = function () {
            return new TagsSpec();
        };

        $app->post("/threads", function (Application $app, Request $req) {
            /** @var ThreadSpec $threadSpec */
            $threadSpec = $app["bbsapi.spec.thread_spec"];
            /** @var TagsSpec $tagsSpec */
            $tagsSpec = $app["bbsapi.spec.tags_spec"];
            /** @var ThreadManagement $service */
            $service = $app["bbsapi.thread.management"];
            /** @var TagRegistration $tagRegistration */
            $tagRegistration = $app["bbsapi.tag.registration"];

            $user = $req->getUser();
            if (!$user) {
                return $app->json([], 401);
            }

            $title = trim($req->request->get("title"));
            $tagNames = $req->request->get("tags");
            if (is_array($tagNames) || is_null($tagNames)) {
                $tags = new Tags($tagNames);
            } else {
                return $app->json(["errors" => ["tags" => "The tags field must be array strings."] ], 400);
            }

            $thread = new Thread();
            $thread->setTitle($title);
            $threadResult = $threadSpec->validate($thread);
            $tagsResult = $tagsSpec->validate($tags);

            if (!$threadResult->isValid() || !$tagsResult->isValid()) {
                $errors = array_merge($threadResult->getErrors(), $tagsResult->getErrors());
                return $app->json([ "errors" => $errors ], 400);
            }

            foreach ($tags as $tag) {
                $thread->addTag($tagRegistration->register($tag));
            }

            $thread = $service->create($thread, $user);

            return $app->json($threadSpec->format($thread));
        });


        $app->get("/threads/{id}", function (Application $app, $id) {
            /** @var ThreadSpec $threadSpec */
            $threadSpec = $app["bbsapi.spec.thread_spec"];
            /** @var ThreadManagement $service */
            $service = $app["bbsapi.thread.management"];
            $thread = $service->findOne($id);

            if (!$thread) {
                return $app->json([], 404);
            }

            return $app->json($threadSpec->format($thread));
        });

        $app->get("/threads", function (Application $app, Request $req) {
            /** @var ThreadSpec $threadSpec */
            $threadSpec = $app["bbsapi.spec.thread_spec"];
            /** @var ThreadManagement $service */
            $service = $app["bbsapi.thread.management"];

            $tags = $req->query->get("tags");
            if (is_null($tags) || $tags === "") {
                return $app->json([]);
            }

            $tags = new Tags(explode(',', $tags));
            $threads = $service->findByTags($tags);

            return $app->json(array_map(function ($thread) use ($threadSpec) {
                return $threadSpec->format($thread);
            }, $threads));
        });


        $app->delete("/threads/{id}", function (Application $app, Request $req, $id) {
            /** @var ThreadManagement $service */
            $service = $app["bbsapi.thread.management"];
            $thread = $service->findOne($id);

            if (!$thread) {
                return $app->json([], 404);
            }

            $user = $req->getUser();
            if (!$user) {
                return $app->json([], 401);
            }

            $result = $service->remove($thread, $user);
            if (!$result) {
                return $app->json([], 403);
            }

            return $app->json([], 200);
        });
    }

    public function boot(Application $app)
    {
    }
}