<?php

use Drupal\node\Entity\Node;

echo "🚀 Creating Sample Blog Posts...\n\n";

$blogs = [
  [
    'title' => 'How to Start Reading Early: Tips for Parents',
    'body' => "Starting reading early is one of the best gifts you can give your child. Research shows that children who are read to from an early age develop better language skills, larger vocabularies, and stronger comprehension abilities.\n\nHere are some effective strategies:\n\n1. **Start from Birth**: It's never too early! Even newborns benefit from hearing your voice and seeing picture books.\n\n2. **Make it a Daily Routine**: Set aside a special reading time each day, preferably at bedtime when children are calm and ready to listen.\n\n3. **Choose Age-Appropriate Books**: Board books for babies, picture books for toddlers, and chapter books as they grow.\n\n4. **Let Them Choose**: Allow children to pick books they're interested in, even if it means reading the same story multiple times.\n\n5. **Use Different Voices**: Make reading fun by using different voices for characters and adding sound effects.\n\n6. **Ask Questions**: Engage your child by asking about the pictures, predicting what happens next, or discussing the story.\n\n7. **Create a Reading Space**: Designate a cozy corner with good lighting and comfortable seating for reading.\n\nRemember, the goal is to make reading enjoyable and create positive associations with books. Happy reading!",
    'short' => 'Discover effective strategies for introducing your child to the wonderful world of reading from an early age.',
  ],
  [
    'title' => 'Building Your Child\'s First Library: Essential Books',
    'body' => "Creating a home library for your child is an investment in their future. Here's our guide to building a collection that will inspire a lifelong love of reading.\n\n**For Ages 0-2:**\n- Board books with simple pictures and textures\n- Books with rhymes and repetitive phrases\n- Touch-and-feel books\n\n**For Ages 3-5:**\n- Picture books with engaging stories\n- Alphabet and counting books\n- Books about feelings and friendship\n\n**For Ages 6-8:**\n- Early reader chapter books\n- Books about diverse topics and cultures\n- Adventure and fantasy stories\n\n**For Ages 9-12:**\n- Middle-grade novels\n- Books that match their interests (sports, science, mystery)\n- Series that encourage continued reading\n\n**Tips for Building Your Library:**\n- Mix classic and contemporary titles\n- Include books with diverse characters and authors\n- Add non-fiction alongside fiction\n- Visit library sales and second-hand bookstores\n- Ask for books as gifts\n\nRemember, quality matters more than quantity. A well-curated collection of 50 books is better than hundreds of random titles.",
    'short' => 'A comprehensive guide to selecting the perfect books for every age and stage of your child\'s reading journey.',
  ],
  [
    'title' => 'The Magic of Reading Aloud: Benefits Beyond Words',
    'body' => "Reading aloud to children is one of the most important activities parents can engage in. It's not just about teaching them to read—it's about building bonds, expanding horizons, and creating memories.\n\n**Cognitive Benefits:**\n- Enhances vocabulary and language development\n- Improves concentration and attention span\n- Develops critical thinking skills\n- Boosts imagination and creativity\n\n**Emotional Benefits:**\n- Strengthens parent-child bonding\n- Provides comfort and security\n- Helps children process emotions\n- Builds confidence and self-esteem\n\n**Social Benefits:**\n- Teaches empathy and understanding\n- Introduces different perspectives and cultures\n- Provides topics for family discussions\n- Builds social awareness\n\n**Tips for Effective Read-Aloud Sessions:**\n1. Choose a quiet, comfortable space\n2. Turn off all distractions\n3. Show enthusiasm in your voice\n4. Point to pictures and words\n5. Pause to discuss the story\n6. Let children ask questions\n7. Reread favorite books often\n\nExperts recommend reading aloud to children until at least age 12, even after they can read independently. The shared experience remains valuable throughout childhood.",
    'short' => 'Explore the profound impact of reading aloud on children\'s development and family relationships.',
  ],
];

foreach ($blogs as $index => $blog_data) {
  $node = Node::create([
    'type' => 'blog',
    'title' => $blog_data['title'],
    'status' => 1,
    'body' => [
      'value' => $blog_data['body'],
      'format' => 'basic_html',
    ],
    'field_short_description' => [
      'value' => $blog_data['short'],
      'format' => 'plain_text',
    ],
  ]);
  
  $node->save();
  echo "✅ Created blog: " . $blog_data['title'] . "\n";
}

echo "\n✅ Sample blogs created successfully!\n";
