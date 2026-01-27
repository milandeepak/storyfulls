<?php

echo "=== COMPLETE BOOK METADATA FROM OLD SITE ===\n\n";

// Book to Age Group mapping (from line 4893-4908)
$book_age_groups = [
  1 => 10,   // tid 10 = '5-8'
  2 => 8,    // tid 8  = '13-16'
  3 => 11,   // tid 11 = '2-5'
  4 => 11,   // tid 11 = '2-5'
  5 => 9,    // tid 9  = '8-12'
  6 => 10,   // tid 10 = '5-8'
  7 => 12,   // tid 12 = '0-2'
  8 => 10,   // tid 10 = '5-8'
  11 => 10,  // tid 10 = '5-8'
  12 => 10,  // tid 10 = '5-8'
  13 => 11,  // tid 11 = '2-5'
  26 => 11,  // tid 11 = '2-5'
  29 => 12,  // tid 12 = '0-2'
  30 => 11,  // tid 11 = '2-5'
  31 => 11,  // tid 11 = '2-5'
  34 => 11,  // tid 11 = '2-5'
];

// Book to Author mapping (from line 4931-4945)
$book_authors = [
  1 => 50,   // Adam Blade
  2 => 43,   // Roald Dahl
  3 => 43,   // Roald Dahl
  4 => 52,   // Ruskin Bond
  5 => 43,   // Roald Dahl
  7 => 52,   // Ruskin Bond
  8 => 65,   // Archer Jeffrey
  11 => 58,  // Elisabetta Dami
  12 => 49,  // R. J. Palacio
  13 => 52,  // Ruskin Bond
  26 => 19,  // Olivia Hope
  29 => 20,  // Tom Percival
  30 => 23,  // Caryl Hart
  31 => 25,  // Laura Purdie Salas
  34 => 37,  // Julia Donaldson
];

// Taxonomy term names (from line 9829-9894)
$age_group_names = [
  8 => '13-16',
  9 => '8-12',
  10 => '5-8',
  11 => '2-5',
  12 => '0-2',
];

$author_names = [
  19 => 'Olivia Hope',
  20 => 'Tom Percival',
  23 => 'Caryl Hart',
  25 => 'Laura Purdie Salas',
  37 => 'Julia Donaldson',
  43 => 'Roald Dahl',
  49 => 'R. J. Palacio',
  50 => 'Adam Blade',
  52 => 'Ruskin Bond',
  58 => 'Elisabetta Dami',
  65 => 'Archer Jeffrey',
];

// Book titles (from line 4079-4113)
$books = [
  1 => 'Brutus the Hound of Horror',
  2 => 'Charlie and the Chocolate Factory',
  3 => 'Matilda',
  4 => 'Tales from the Childhood',
  5 => 'The BFG',
  6 => 'The Boy At the Back of the Class by Onjali Q. Rauf',
  7 => 'The Whistling Schoolboy And Other Stories Of School Life',
  8 => 'Willy and the Killer Kipper',
  11 => 'The Sewer Rat Stink',
  12 => 'Wonder',
  13 => 'Prankenstein: The Book of Crazy Mischief',
  26 => 'Be Wild, Little One',
  29 => "Billy's Bravery",
  30 => 'Meet the Weather',
  31 => 'Zap! Clap! Boom!',
  34 => 'The Gruffalo',
];

// Cover images (from line 5045-5062)
$book_covers = [
  1 => '1.jpg',
  2 => '2.jpg',
  3 => '3.jpg',
  4 => '4.jpg',
  5 => '5.jpg',
  6 => '6.jpg',
  7 => '7.jpg',
  8 => '8.jpg',
  11 => '1_0.jpg',
  12 => '2_0.jpg',
  13 => '11.jpg',
  26 => 'Be Wild, Little One.jpeg',
  29 => "Billy's Bravery.jpeg",
  30 => 'Meet the Weather.jpeg',
  31 => 'Zap! Clap! Boom!.jpeg',
  34 => 'Think Big.jpeg', // The Gruffalo
];

echo str_pad("", 120, "=") . "\n";
echo str_pad("Book Title", 50) . str_pad("Author", 25) . str_pad("Age Group", 15) . "Cover Image\n";
echo str_pad("", 120, "=") . "\n";

foreach ($books as $nid => $title) {
  $author_tid = $book_authors[$nid] ?? null;
  $author = $author_tid ? $author_names[$author_tid] : 'Unknown';
  
  $age_tid = $book_age_groups[$nid] ?? null;
  $age_group = $age_tid ? $age_group_names[$age_tid] : 'Unknown';
  
  $cover = $book_covers[$nid] ?? 'No cover';
  
  echo str_pad(substr($title, 0, 48), 50);
  echo str_pad($author, 25);
  echo str_pad($age_group, 15);
  echo $cover . "\n";
}

echo str_pad("", 120, "=") . "\n";
echo "\n✅ All book metadata extracted successfully!\n\n";

echo "SUMMARY:\n";
echo "- Total Books: " . count($books) . "\n";
echo "- Books with Authors: " . count($book_authors) . "\n";
echo "- Books with Age Groups: " . count($book_age_groups) . "\n";
echo "- Books with Cover Images: " . count($book_covers) . "\n";
