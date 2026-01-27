<?php

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\File\FileExists;

echo "🚀 Resetting Blogs (delete samples, create full blogs with images/tags)...\n\n";

// 1) Ensure tags vocabulary exists (core often uses 'tags').
$vocab = Vocabulary::load('tags');
if (!$vocab) {
  $vocab = Vocabulary::create([
    'vid' => 'tags',
    'name' => 'Tags',
    'description' => 'General tags',
  ]);
  $vocab->save();
  echo "✓ Created vocabulary: tags\n";
}

// 2) Ensure field_tags storage exists and is attached to blog bundle.
$storage = FieldStorageConfig::loadByName('node', 'field_tags');
if (!$storage) {
  $storage = FieldStorageConfig::create([
    'field_name' => 'field_tags',
    'entity_type' => 'node',
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => ['target_type' => 'taxonomy_term'],
  ]);
  $storage->save();
  echo "✓ Created field storage: field_tags\n";
}

$field_config = FieldConfig::loadByName('node', 'blog', 'field_tags');
if (!$field_config) {
  FieldConfig::create([
    'field_storage' => $storage,
    'bundle' => 'blog',
    'label' => 'Tags',
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => ['target_bundles' => ['tags' => 'tags']],
    ],
  ])->save();
  echo "✓ Attached field_tags to blog\n";
}

// 3) Delete sample blogs created earlier (match by titles).
$sample_titles = [
  'How to Start Reading Early: Tips for Parents',
  "Building Your Child's First Library: Essential Books",
  'The Magic of Reading Aloud: Benefits Beyond Words',
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
$query = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'blog')
  ->condition('title', $sample_titles, 'IN');

$nids = $query->execute();
if (!empty($nids)) {
  $nodes = $storage->loadMultiple($nids);
  foreach ($nodes as $node) {
    $title = $node->getTitle();
    $nid = $node->id();
    $node->delete();
    echo "🗑️ Deleted sample blog: {$title} (nid {$nid})\n";
  }
} else {
  echo "(No sample blogs found to delete.)\n";
}

echo "\n";

// 4) Create / load tags.
$tag_names = ['Reading', 'Parents', 'Tips', 'Literacy', 'Books'];
$tag_ids = [];
foreach ($tag_names as $name) {
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => 'tags', 'name' => $name]);
  if ($existing) {
    $tag_ids[$name] = reset($existing)->id();
    continue;
  }
  $term = Term::create(['vid' => 'tags', 'name' => $name]);
  $term->save();
  $tag_ids[$name] = $term->id();
  echo "✓ Created tag: {$name}\n";
}

// 5) Create a File entity from the theme feature image and reuse it.
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
    echo "✓ Imported blog feature image to {$file_entity->getFileUri()}\n";
  }
} else {
  echo "⚠️ Feature image not found at {$source_path} (will create blogs without image)\n";
}

$blogs = [
  [
    'title' => 'How to Start Reading Early: Tips for Parents',
    'tags' => ['Reading', 'Parents', 'Tips'],
    'body' => "<p>Starting early makes reading feel natural. Even a few minutes a day builds vocabulary, attention span, and a strong bond.</p>
<p><strong>Try this:</strong></p>
<ul>
<li>Keep books visible and within reach</li>
<li>Make it part of bedtime</li>
<li>Let kids choose the story</li>
<li>Re-read favorites (repetition helps!)</li>
</ul>
<p>Most importantly: keep it playful and pressure-free.</p>",
  ],
  [
    'title' => 'Building Your Child\'s First Library',
    'tags' => ['Books', 'Parents', 'Literacy'],
    'body' => "<p>A small, well-loved library beats a huge shelf of random books. Pick stories that match your child’s interests and mix fiction with non-fiction.</p>
<p><strong>Quick checklist:</strong></p>
<ul>
<li>Diverse characters and cultures</li>
<li>Different reading levels</li>
<li>Poetry / rhymes</li>
<li>Fun facts and picture dictionaries</li>
</ul>
<p>Rotate books often to keep the shelf feeling fresh.</p>",
  ],
  [
    'title' => 'The Magic of Reading Aloud',
    'tags' => ['Reading', 'Literacy', 'Tips'],
    'body' => "<p>Reading aloud is not just for little kids. It boosts comprehension, builds empathy, and creates a shared family ritual.</p>
<p><strong>Make it better:</strong></p>
<ul>
<li>Use voices and pauses</li>
<li>Ask “what do you think happens next?”</li>
<li>Talk about feelings and choices</li>
</ul>
<p>Even 10 minutes a day makes a difference.</p>",
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

echo "\n✅ Done.\n";
