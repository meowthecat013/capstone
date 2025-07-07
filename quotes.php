<?php
require_once 'config.php';

header('Content-Type: application/json');

$quotes = [
    "Every day may not be good, but there's something good in every day.",
    "Small steps every day lead to big results.",
    "Recovery is not a race. You don't have to feel guilty if it takes you longer than you thought it would.",
    "You're braver than you believe, stronger than you seem, and smarter than you think.",
    "Healing is an art. It takes time, it takes practice, it takes love.",
    "The only way out is through.",
    "Progress, not perfection.",
    "You are not your illness. You have an individual story to tell. You have a name, a history, a personality. Staying yourself is part of the battle.",
    "Hope is being able to see that there is light despite all of the darkness.",
    "You don't have to control your thoughts. You just have to stop letting them control you."
];

$randomQuote = $quotes[array_rand($quotes)];

echo json_encode(['quote' => $randomQuote]);
?>