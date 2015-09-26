<?php
namespace Kumatch\BBSAPI\Application\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Kumatch\BBSAPI\Application\Request;
use Kumatch\BBSAPI\UseCase\ThreadManagement;
use Kumatch\BBSAPI\UseCase\ThreadPostManagement;
use Kumatch\BBSAPI\UseCase\TagRegistration;
use Kumatch\BBSAPI\Entity\Thread;
use Kumatch\BBSAPI\Entity\Post;
use Kumatch\BBSAPI\Value\Tags;
use Kumatch\BBSAPI\Spec\ThreadSpec;
use Kumatch\BBSAPI\Spec\PostSpec;
use Kumatch\BBSAPI\Spec\TagsSpec;

class BBSAPIThreadServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["bbsapi.thread.management"] = function (Application $app) {
            return new ThreadManagement($app["entity_manager"]);
        };
        $app["bbsapi.thread.post_management"] = function (Application $app) {
            return new ThreadPostManagement($app["entity_manager"]);
        };
        $app["bbsapi.tag.registration"] = function (Application $app) {
            return new TagRegistration($app["entity_manager"]);
        };

        $app["bbsapi.spec.thread_spec"] = function () {
            return new ThreadSpec();
        };
        $app["bbsapi.spec.post_spec"] = function () {
            return new PostSpec();
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

            return $app->json($threadSpec->format($thread), 201);
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
        })->assert('id', '^\d+$');

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
        })->assert('id', '^\d+$');


        $app->post("/threads/{id}/posts", function (Application $app, Request $req, $id) {
            /** @var ThreadManagement $threadService */
            $threadService = $app["bbsapi.thread.management"];
            /** @var ThreadPostManagement $postService */
            $postService = $app["bbsapi.thread.post_management"];
            /** @var PostSpec $postSpec */
            $postSpec = $app["bbsapi.spec.post_spec"];

            $thread = $threadService->findOne($id);
            if (!$thread) {
                return $app->json([], 404);
            }

            $content = trim($req->request->get("content"));
            $post = new Post();
            $post->setContent($content);

            $result = $postSpec->validate($post);
            if (!$result->isValid()) {
                return $app->json([ "errors" => $result->getErrors() ], 400);
            }

            $post = $postService->register($thread, $post);

            return $app->json($postSpec->format($post), 201);
        })->assert('id', '^\d+$');

        $app->get("/threads/{id}/posts", function (Application $app, $id) {
            /** @var ThreadManagement $threadService */
            $threadService = $app["bbsapi.thread.management"];
            /** @var PostSpec $postSpec */
            $postSpec = $app["bbsapi.spec.post_spec"];

            $thread = $threadService->findOne($id);
            if (!$thread) {
                return $app->json([], 404);
            }

            return $app->json(array_map(function ($post) use ($postSpec) {
                /** @var Post $post */
                return $postSpec->format($post);
            }, $thread->getPosts()), 200);
        })->assert('id', '^\d+$');


        $app->get("/threads/{threadId}/posts/{postId}", function (Application $app, $threadId, $postId) {
            /** @var ThreadManagement $threadService */
            $threadService = $app["bbsapi.thread.management"];
            /** @var PostSpec $postSpec */
            $postSpec = $app["bbsapi.spec.post_spec"];

            $thread = $threadService->findOne($threadId);
            if (!$thread) {
                return $app->json([], 404);
            }

            $post = $thread->getPost($postId);
            if (!$post) {
                return $app->json([], 404);
            }

            return $app->json($postSpec->format($post), 200);
        })->assert('threadId', '^\d+$')->assert('postId', '^\d+$');

    }

    public function boot(Application $app)
    {
    }
}