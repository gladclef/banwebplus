package main.java.scraping;

import main.java.structure.Semester;
import main.java.structure.Subject;

/**
 * Indicates that there are no courses found for the registered semester and
 * subject.
 */
public class NoCoursesException extends Exception {
	private static final long serialVersionUID = 4243852685633315992L;
	public Semester semester;
	public Subject subject;

	public NoCoursesException(Semester semester, Subject subject)
	{
		super("No courses for " + semester + ", " + subject);
		this.semester = semester;
		this.subject = subject;
	}
}
