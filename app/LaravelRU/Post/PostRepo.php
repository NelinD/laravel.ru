<?php namespace LaravelRU\Post;

use LaravelRU\Core\Repository\BaseRepository;
use Post;

class PostRepo extends BaseRepository {

	public function __construct(Post $post)
	{
		$this->model = $post;
	}

	/**
	 * ID автора поста
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function getAuthorId($post_id)
	{
		$author_id = $this->model->where('id', $post_id)->pluck('author_id');

		return $author_id;
	}

	public function getAuthorIdBySlug($slug)
	{
		$author_id = $this->model->where('slug', $slug)->pluck('author_id');

		return $author_id;
	}

	/**
	 * Получить пост по урлу
	 *
	 * @param $slug
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */
	public function getBySlug($slug)
	{
		$post = $this->model->where('slug', $slug)->with('author')->first();

		return $post;
	}

	/**
	 * Последние посты
	 *
	 * @param int $num
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getLastPosts($num = 10)
	{
		return $this->model->notDraft()->with('author')->orderBy(Post::PUBLISHED_AT, 'desc')
			->limit($num)->get();
	}

	/**
	 * html-селектор выбора версии фрейворка в посте
	 */
	public function getFrameworkVersionSelect($currentFrameworkVersion)
	{
		$allVersions = \Version::all();
	}

}
