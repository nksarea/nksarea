<?php

/** Erstellt eine Kommentarliste aus dem Kommentarobjekt
 *
 * @param object $comments Kommentarlistenobjekt
 * @param Template $template Template objekt, dem die Kommentare hinzugefügt werden sollen
 * @param integer $indent Einrückungslevel (für Rekursion)
 * @author Cédric Neukom
 */
function createCommentList($comments, $template, $indent = 0) {
	if(!$template instanceof Template)
		return false;

	foreach($comments as $comment) {
		$template->assignFromNew('comments', 'comment.xhtml', array(
			'content' => $comment->text,
			'author' => $comment->author,
			'date' => $comment->date,
			'id' => $comment->id,
			'indent' => $indent?' data-reply="'.$indent.'"':''
		));
		createCommentList($comment->replies, $template, $indent+1);
	}

	return true;
}