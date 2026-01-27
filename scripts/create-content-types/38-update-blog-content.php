<?php

use Drupal\node\Entity\Node;

echo "🚀 Updating Blog Content...\n\n";

// Load the blog node (nid 585)
$node = Node::load(585);

if ($node) {
  $full_content = "<p>In a world filled with fairy tales and fantasy, where happily ever afters are the norm, Jacqueline Wilson's stories stand out as beacons of reality. Her books, beloved by children and by parents, explore the complexities of growing up in a way that is both accessible and resonant. Her stories provide young readers with a lens through which they can understand the often difficult and sensitive issues they may face in their own lives. Whether it's dealing with divorce in The Suitcase Kid, navigating life in foster care in Dustbin Baby, or coping with a parent's mental health struggles in The Illustrated Mum, Wilson doesn't shy away from portraying the complexities of family life.</p>

<p>These stories are tools for children to understand that families come in all shapes and sizes, and that it's okay if theirs doesn't look like the traditional norm. By reading about characters who face similar challenges, children can feel less isolated in their experiences. Wilson's honest portrayal of family issues helps young readers develop empathy and resilience, equipping them with the tools needed to navigate their own family dynamics.</p>

<p>Friendship is another central theme in Wilson's work. In books like The Lottie Project and Best Friends, she captures the joys and trials of childhood friendships, showing how they evolve and sometimes falter. She addresses bullying, peer pressure, and the pain of losing a friend with sensitivity and realism.</p>

<p>One of the most remarkable aspects of her writing is her ability to tackle real-life challenges that many children face today like poverty, neglect, and illness. In The Bed and Breakfast Star, for example, she addresses the harsh realities of homelessness, while Lola Rose deals with the impact of domestic abuse.</p>

<p>By exposing children to these difficult topics through stories, Wilson allows them to confront and process these issues in a safe and manageable way. Her characters often face adversity with courage and resilience, showing readers that while life can be tough, it's possible to overcome challenges and emerge stronger. This can be incredibly empowering for children.</p>

<h3>Why Children Should Read Jacqueline Wilson</h3>

<p>Jacqueline Wilson's stories are not just tales for entertainment, they are essential reading for young minds navigating the complexities of growing up. In a world where children are often sheltered from harsh realities, her books offer a balanced view, showing that while life can be challenging, it is also full of hope, love, and possibilities. Encouraging children to read Jacqueline Wilson's stories is a way of preparing them for the real world.</p>

<p>In today's society, where children are exposed to an overwhelming amount of information, it's more important than ever to provide them with stories that reflect real-life issues in a thoughtful and accessible way. By getting our children to read these stories, we are helping them develop a deeper understanding of themselves and the world they live in, preparing them to face life's challenges with empathy, courage, and resilience.</p>";

  $node->set('body', [
    'value' => $full_content,
    'format' => 'basic_html',
  ]);
  
  $node->save();
  
  echo "✅ Updated blog content for: {$node->getTitle()} (nid {$node->id()})\n";
} else {
  echo "❌ Blog node 585 not found\n";
}

echo "\n✅ Done!\n";
