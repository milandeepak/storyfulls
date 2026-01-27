<?php

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileExists;

echo "🚀 Recreating Blogs with Full Content...\n\n";

// 1) Delete all existing blog nodes
$storage = \Drupal::entityTypeManager()->getStorage('node');
$query = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'blog');

$nids = $query->execute();
if (!empty($nids)) {
  $nodes = $storage->loadMultiple($nids);
  foreach ($nodes as $node) {
    $title = $node->getTitle();
    $node->delete();
    echo "🗑️ Deleted blog: {$title}\n";
  }
}

echo "\n";

// 2) Ensure tags exist
$tag_names = ['BlrLitFest'];
$tag_ids = [];
foreach ($tag_names as $name) {
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => 'tags', 'name' => $name]);
  if ($existing) {
    $tag_ids[$name] = reset($existing)->id();
  } else {
    $term = Term::create(['vid' => 'tags', 'name' => $name]);
    $term->save();
    $tag_ids[$name] = $term->id();
    echo "✓ Created tag: {$name}\n";
  }
}

// 3) Import feature image
$relative = 'themes/custom/storyfulls/images/blogindividualpagefeatureimage.png';
$source_path = DRUPAL_ROOT . '/' . $relative;

$file_entity = NULL;
if (file_exists($source_path)) {
  $data = file_get_contents($source_path);
  $destination = 'public://blogindividualpagefeatureimage.png';
  /** @var \Drupal\file\FileRepositoryInterface $file_repo */
  $file_repo = \Drupal::service('file.repository');
  $file_entity = $file_repo->writeData($data, $destination, FileExists::Replace);
  if ($file_entity) {
    $file_entity->setPermanent();
    $file_entity->save();
    echo "✓ Imported blog feature image\n";
  }
}

echo "\n";

// 4) Create blogs with full Jacqueline Wilson content
$blogs = [
  [
    'title' => "The Power of Jacqueline Wilson's Stories: Teaching Children About the Realities of Life",
    'body' => "<p>In a world filled with fairy tales and fantasy, where happily ever afters are the norm, Jacqueline Wilson's stories stand out as beacons of reality. Her books, beloved by children and by parents, explore the complexities of growing up in a way that is both accessible and resonant. Her stories provide young readers with a lens through which they can understand the often difficult and sensitive issues they may face in their own lives. Whether it's dealing with divorce in The Suitcase Kid, navigating life in foster care in Dustbin Baby, or coping with a parent's mental health struggles in The Illustrated Mum, Wilson doesn't shy away from portraying the complexities of family life.</p>

<p>These stories are tools for children to understand that families come in all shapes and sizes, and that it's okay if theirs doesn't look like the traditional norm. By reading about characters who face similar challenges, children can feel less isolated in their experiences. Wilson's honest portrayal of family issues helps young readers develop empathy and resilience, equipping them with the tools needed to navigate their own family dynamics.</p>

<p>Friendship is another central theme in Wilson's work. In books like The Lottie Project and Best Friends, she captures the joys and trials of childhood friendships, showing how they evolve and sometimes falter. She addresses bullying, peer pressure, and the pain of losing a friend with sensitivity and realism.</p>

<p>One of the most remarkable aspects of her writing is her ability to tackle real-life challenges that many children face today like poverty, neglect, and illness. In The Bed and Breakfast Star, for example, she addresses the harsh realities of homelessness, while Lola Rose deals with the impact of domestic abuse.</p>

<p>By exposing children to these difficult topics through stories, Wilson allows them to confront and process these issues in a safe and manageable way. Her characters often face adversity with courage and resilience, showing readers that while life can be tough, it's possible to overcome challenges and emerge stronger. This can be incredibly empowering for children.</p>

<h3>Why Children Should Read Jacqueline Wilson</h3>

<p>Jacqueline Wilson's stories are not just tales for entertainment, they are essential reading for young minds navigating the complexities of growing up. In a world where children are often sheltered from harsh realities, her books offer a balanced view, showing that while life can be challenging, it is also full of hope, love, and possibilities. Encouraging children to read Jacqueline Wilson's stories is a way of preparing them for the real world.</p>

<p>In today's society, where children are exposed to an overwhelming amount of information, it's more important than ever to provide them with stories that reflect real-life issues in a thoughtful and accessible way. By getting our children to read these stories, we are helping them develop a deeper understanding of themselves and the world they live in, preparing them to face life's challenges with empathy, courage, and resilience.</p>",
    'tags' => ['BlrLitFest'],
  ],
];

foreach ($blogs as $blog) {
  $node = Node::create([
    'type' => 'blog',
    'title' => $blog['title'],
    'status' => 1,
    'body' => [
      'value' => $blog['body'],
      'format' => 'basic_html',
    ],
  ]);

  if ($file_entity) {
    $node->set('field_featured_image', [
      'target_id' => $file_entity->id(),
      'alt' => $blog['title'],
    ]);
  }

  $node->set('field_tags', array_map(static function ($name) use ($tag_ids) {
    return ['target_id' => $tag_ids[$name]];
  }, $blog['tags']));

  $node->save();
  echo "✅ Created blog: {$blog['title']} (nid {$node->id()})\n";
}

echo "\n✅ Done. Full blogs with complete content created.\n";
